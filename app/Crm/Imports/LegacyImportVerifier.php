<?php

namespace App\Crm\Imports;

use Illuminate\Support\Facades\DB;

class LegacyImportVerifier
{
    public const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

    public function verify(LegacyExport $export, int $batchId): VerificationReport
    {
        $errors = [];
        $expected = [];
        foreach ($export->tables as $table => $rows) {
            foreach ($rows as $row) {
                $expected[$table.'|'.$row['id']] = hash('sha256', json_encode($row, self::JSON_FLAGS));
            }
        }

        $records = DB::table('crm_legacy_records')->where('batch_id', $batchId)->get(['source_table', 'source_id', 'payload', 'payload_sha256']);
        if ($records->count() !== $export->totalRows()) {
            $errors[] = 'Raw row count differs from source.';
        }

        foreach ($records as $record) {
            $key = $record->source_table.'|'.$record->source_id;
            $storedHash = hash('sha256', $record->payload);
            if (! isset($expected[$key]) || ! hash_equals($expected[$key], $storedHash) || ! hash_equals($storedHash, $record->payload_sha256)) {
                $errors[] = 'Raw payload mismatch for '.$key;
            }
            unset($expected[$key]);
        }
        foreach (array_keys($expected) as $key) {
            $errors[] = 'Missing raw row '.$key;
        }

        $leadIds = array_column($export->tables['leads'], 'id');
        $sites = DB::table('crm_sites')->whereIn('legacy_lead_id', $leadIds)->get(['legacy_lead_id', 'name', 'area', 'location_type', 'map_url', 'source', 'notes', 'legacy_owner_label']);
        if ($sites->count() !== count($leadIds)) {
            $errors[] = 'Projected Site count differs from source.';
        }
        $sitesByLead = $sites->keyBy('legacy_lead_id');
        foreach ($export->tables['leads'] as $lead) {
            $site = $sitesByLead->get($lead['id']);
            if (! $site) {
                $errors[] = 'Missing Site projection '.$lead['id'];

                continue;
            }
            foreach (['name' => 'site_name', 'area' => 'area', 'location_type' => 'location_type', 'map_url' => 'map_url', 'source' => 'lead_source', 'notes' => 'notes', 'legacy_owner_label' => 'assigned_to'] as $siteField => $legacyField) {
                if (($site->{$siteField} ?? null) !== ($lead[$legacyField] ?? null)) {
                    $errors[] = 'Site field mismatch '.$lead['id'].' '.$siteField;
                }
            }
        }

        $namedLeads = array_values(array_filter($export->tables['leads'], fn (array $lead) => trim((string) ($lead['contact_name'] ?? '')) !== ''));
        $contacts = DB::table('crm_contacts')->whereIn('legacy_lead_id', array_column($namedLeads, 'id'))->get(['legacy_lead_id', 'name', 'role', 'phone', 'line_name', 'email', 'is_primary']);
        if ($contacts->count() !== count($namedLeads)) {
            $errors[] = 'Projected contact count differs from source.';
        }
        $contactsByLead = $contacts->keyBy('legacy_lead_id');
        foreach ($namedLeads as $lead) {
            $contact = $contactsByLead->get($lead['id']);
            if (! $contact) {
                $errors[] = 'Missing contact projection '.$lead['id'];

                continue;
            }
            foreach (['name' => 'contact_name', 'role' => 'contact_role', 'phone' => 'phone', 'line_name' => 'line_name', 'email' => 'email'] as $contactField => $legacyField) {
                if (($contact->{$contactField} ?? null) !== ($lead[$legacyField] ?? null)) {
                    $errors[] = 'Contact field mismatch '.$lead['id'].' '.$contactField;
                }
            }
            if (! (bool) $contact->is_primary) {
                $errors[] = 'Imported contact is not primary '.$lead['id'];
            }
        }

        return new VerificationReport($errors === [], $records->count(), $errors);
    }
}
