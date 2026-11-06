<?php

namespace App\Crm\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnsureEngineeringProject
{
    public function forLead(string $sourceId, ?int $batchId): array
    {
        $existing = DB::table('crm_engineering_projects')->where('lead_source_id', $sourceId)->first();
        if ($existing !== null) {
            return (array) $existing;
        }

        $legacy = [];
        foreach (DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'projects')->get(['payload']) as $record) {
            $project = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
            if (($project['lead_id'] ?? null) === $sourceId) {
                $legacy[] = $project;
            }
        }
        if (count($legacy) > 1) {
            abort(409, __('crm_errors.duplicate_project_before_stage'));
        }

        $imported = $legacy[0] ?? null;
        $code = $imported['project_code'] ?? 'GSB-CRM-'.Str::ulid();
        $id = DB::table('crm_engineering_projects')->insertGetId([
            'lead_source_id' => $sourceId,
            'project_code' => $code,
            'legacy_project_id' => $imported['id'] ?? null,
            'engineering_stage' => $imported['engineering_stage'] ?? 'NOT_STARTED',
            'sync_status' => $imported === null ? 'pending_connection' : ($imported['sync_status'] ?? 'legacy_unknown'),
            'external_project_id' => $imported['external_project_id'] ?? null,
            'origin' => $imported === null ? 'current' : 'legacy',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if ($imported === null) {
            DB::table('crm_integration_outbox')->insert([
                'engineering_project_id' => $id,
                'event_type' => 'crm.project.created',
                'dedupe_key' => 'crm.project.created:'.$id,
                'status' => 'pending_connection',
                'payload' => json_encode(['lead_source_id' => $sourceId, 'project_code' => $code], JSON_THROW_ON_ERROR),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return (array) DB::table('crm_engineering_projects')->find($id);
    }
}
