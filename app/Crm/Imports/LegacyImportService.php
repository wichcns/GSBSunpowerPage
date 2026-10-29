<?php

namespace App\Crm\Imports;

use App\Crm\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LegacyImportService
{
    public function import(LegacyExport $export, string $sourcePath): ImportResult
    {
        $this->assertLocalTarget();
        if (! is_file($sourcePath) || ! hash_equals($export->sourceSha256, hash_file('sha256', $sourcePath))) {
            throw new RuntimeException('Source SHA changed after validation.');
        }

        $existing = DB::table('crm_legacy_import_batches')->where('source_sha256', $export->sourceSha256)->first();
        if ($existing) {
            $verified = (new LegacyImportVerifier)->verify($export, $existing->id);
            $copyPath = $this->copyPath($export);
            if (! $verified->valid || ! Storage::disk('local')->exists($copyPath) ||
                ! hash_equals($export->sourceSha256, hash('sha256', Storage::disk('local')->get($copyPath)))) {
                throw new RuntimeException('Existing imported batch failed verification.');
            }

            return $this->result($export, $existing->id, $verified->verifiedRows, true);
        }

        if (DB::table('crm_sites')->whereNotNull('legacy_lead_id')->exists()) {
            throw new RuntimeException('A different export already owns legacy Sites; review conflicts before importing.');
        }

        $copyPath = $this->copyPath($export);
        $sourceBytes = file_get_contents($sourcePath);
        if ($sourceBytes === false || ! Storage::disk('local')->put($copyPath, $sourceBytes) ||
            ! hash_equals($export->sourceSha256, hash('sha256', Storage::disk('local')->get($copyPath)))) {
            throw new RuntimeException('Private source copy failed SHA verification.');
        }

        $batchId = DB::transaction(function () use ($export, $sourcePath) {
            $now = now();
            $batchId = DB::table('crm_legacy_import_batches')->insertGetId([
                'source_sha256' => $export->sourceSha256,
                'source_name' => basename($sourcePath),
                'exported_at' => $export->exportedAt,
                'source_counts' => json_encode($export->counts, JSON_THROW_ON_ERROR),
                'imported_counts' => json_encode([], JSON_THROW_ON_ERROR),
                'validation_report' => json_encode([], JSON_THROW_ON_ERROR),
                'status' => 'importing',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($export->tables as $table => $rows) {
                foreach ($rows as $row) {
                    $payload = json_encode($row, LegacyImportVerifier::JSON_FLAGS);
                    DB::table('crm_legacy_records')->insert([
                        'batch_id' => $batchId,
                        'source_table' => $table,
                        'source_id' => $row['id'],
                        'payload' => $payload,
                        'payload_sha256' => hash('sha256', $payload),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            foreach ($export->tables['leads'] as $lead) {
                $site = Site::create([
                    'legacy_lead_id' => $lead['id'],
                    'legacy_owner_label' => $lead['assigned_to'] ?? null,
                    'name' => $lead['site_name'],
                    'area' => $lead['area'] ?? null,
                    'location_type' => $lead['location_type'] ?? null,
                    'map_url' => $lead['map_url'] ?? null,
                    'source' => $lead['lead_source'] ?? null,
                    'notes' => $lead['notes'] ?? null,
                ]);
                $site->update(['site_code' => sprintf('SITE-%06d', $site->id)]);
                if (trim((string) ($lead['contact_name'] ?? '')) !== '') {
                    $site->contacts()->create([
                        'legacy_lead_id' => $lead['id'],
                        'name' => $lead['contact_name'],
                        'role' => $lead['contact_role'] ?? null,
                        'phone' => $lead['phone'] ?? null,
                        'line_name' => $lead['line_name'] ?? null,
                        'email' => $lead['email'] ?? null,
                        'is_primary' => true,
                    ]);
                }
                $site->audits()->create([
                    'actor_user_id' => null,
                    'action' => 'imported',
                    'changed_fields' => ['legacy_lead_id'],
                ]);
            }

            $verified = (new LegacyImportVerifier)->verify($export, $batchId);
            if (! $verified->valid) {
                throw new RuntimeException('Pre-commit verification failed: '.implode('; ', $verified->errors));
            }

            DB::table('crm_legacy_import_batches')->where('id', $batchId)->update([
                'imported_counts' => json_encode($export->counts, JSON_THROW_ON_ERROR),
                'validation_report' => json_encode([
                    'verified_rows' => $verified->verifiedRows,
                    'sites' => count($export->tables['leads']),
                    'contacts' => $export->namedContacts(),
                    'nameless_contact_exceptions' => $export->namelessContactExceptions(),
                    'unresolved_owners' => $export->unresolvedOwners(),
                ], JSON_THROW_ON_ERROR),
                'status' => 'completed',
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

            return $batchId;
        });

        $verified = (new LegacyImportVerifier)->verify($export, $batchId);
        if (! $verified->valid) {
            throw new RuntimeException('Post-commit verification failed: '.implode('; ', $verified->errors));
        }

        return $this->result($export, $batchId, $verified->verifiedRows, false);
    }

    private function result(LegacyExport $export, int $batchId, int $verifiedRows, bool $alreadyImported): ImportResult
    {
        return new ImportResult(
            $batchId,
            $export->sourceSha256,
            $verifiedRows,
            count($export->tables['leads']),
            $export->namedContacts(),
            $export->namelessContactExceptions(),
            $export->unresolvedOwners(),
            $alreadyImported,
        );
    }

    private function copyPath(LegacyExport $export): string
    {
        return 'legacy-imports/'.$export->sourceSha256.'/database.json';
    }

    private function assertLocalTarget(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        if (app()->environment('testing') && $connection === 'sqlite' && $database === ':memory:') {
            return;
        }
        if (! in_array($connection, ['mysql', 'crm'], true) || $database !== 'gsb_ev_crm_local' ||
            config("database.connections.{$connection}.host") !== '127.0.0.1') {
            throw new RuntimeException('Import is restricted to local MySQL database gsb_ev_crm_local.');
        }
    }
}
