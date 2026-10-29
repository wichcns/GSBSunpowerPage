<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SiteContactPreview
{
    /** @return array<int, array{name: string, source: string}> */
    public function forSites(Collection $sites): array
    {
        $missing = $sites->filter(fn (Site $site) => $site->legacy_lead_id !== null && $site->contacts->isEmpty());
        if ($missing->isEmpty()) {
            return [];
        }

        $leadIds = $missing->pluck('legacy_lead_id')->unique()->values()->all();
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $legacy = $batchId === null ? collect() : DB::table('crm_legacy_records')
            ->where('batch_id', $batchId)->where('source_table', 'leads')
            ->whereIn('source_id', $leadIds)->get(['source_id', 'payload'])->keyBy('source_id');
        $current = DB::table('crm_lead_details')->whereIn('lead_source_id', $leadIds)
            ->get(['lead_source_id', 'profile'])->keyBy('lead_source_id');
        $previews = [];

        foreach ($missing as $site) {
            $detail = $current->get($site->legacy_lead_id);
            $profile = $detail === null ? [] : json_decode($detail->profile, true, 512, JSON_THROW_ON_ERROR);
            $record = $legacy->get($site->legacy_lead_id);
            $import = $record === null ? [] : json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
            $source = array_key_exists('contact_name', $profile) ? 'current' : 'legacy';
            $name = $source === 'current' ? $profile['contact_name'] : ($import['contact_name'] ?? null);
            if (is_string($name) && trim($name) !== '') {
                $previews[$site->id] = ['name' => $name, 'source' => $source];
            }
        }

        return $previews;
    }
}
