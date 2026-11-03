<?php

namespace App\Crm\ReadModels;

use Illuminate\Support\Facades\DB;

class EngineeringConstruction
{
    public function forProject(string $sourceId): ?array
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $lead = $batchId === null ? null : DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->where('source_id', $sourceId)->first(['payload']);
        if ($lead === null) {
            $lead = DB::table('crm_native_opportunities')
                ->where('source_id', $sourceId)
                ->first(['payload']);
        }

        if ($lead === null) {
            return null;
        }

        $legacy = json_decode($lead->payload, true, 512, JSON_THROW_ON_ERROR);
        // $details = DB::table('crm_lead_details')->where('lead_source_id', $sourceId)->value('profile');
        // $profile = $details === null ? [] : json_decode($details, true, 512, JSON_THROW_ON_ERROR);
        // $installation = array_key_exists('installation_status', $profile)
        //     ? $profile['installation_status'] : ($legacy['installation_status'] ?? null);
        $details = DB::table('crm_lead_details')
            ->where('lead_source_id', $sourceId)
            ->first(['profile', 'technical']);

        $profile = $details?->profile
            ? json_decode($details->profile, true, 512, JSON_THROW_ON_ERROR)
            : [];

        $technical = $details?->technical
            ? json_decode($details->technical, true, 512, JSON_THROW_ON_ERROR)
            : [];

        $installation = $technical['installation_status']
            ?? $profile['installation_status']
            ?? $legacy['installation_status']
            ?? null;

        $record = DB::table('crm_engineering_construction_records')->where('lead_source_id', $sourceId)->first();
        if ($record === null) {
            return [
                'status' => 'not_recorded',
                'owner_user_id' => null,
                'owner_name' => null,
                'started_on' => null,
                'target_finish_on' => null,
                'version' => null,
                'installation_status' => $installation,
                'updates' => [],
            ];
        }

        $owner = DB::table('crm_users')->where('id', $record->owner_user_id)->value('name');
        $updates = DB::table('crm_engineering_construction_updates as u')
            ->leftJoin('crm_users as actor', 'actor.id', '=', 'u.actor_user_id')
            ->where('u.construction_record_id', $record->id)->orderByDesc('u.id')
            ->get(['u.id', 'u.note', 'u.created_at', 'actor.name as actor_name'])
            ->map(fn($row) => (array) $row)->all();

        return [
            'status' => 'recorded',
            'owner_user_id' => $record->owner_user_id,
            'owner_name' => $owner,
            'started_on' => $record->started_on,
            'target_finish_on' => $record->target_finish_on,
            'version' => 'current:' . $record->version,
            'installation_status' => $installation,
            'updates' => $updates,
        ];
    }
}
