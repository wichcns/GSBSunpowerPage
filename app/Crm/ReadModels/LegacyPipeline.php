<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use App\Crm\Support\SalesStages;
use Illuminate\Support\Facades\DB;

class LegacyPipeline
{
    public const STAGES = [
        'NEW' => 'ลูกค้าเป้าหมายใหม่',
        'NEGOTIATING' => 'ติดต่อแล้ว / อยู่ระหว่างเจรจา',
        'REVIEW' => 'ประเมินภายใน',
        'MOU' => 'ขั้นตอน MOU',
        'CONTRACT' => 'ขั้นตอนสัญญา',
        'SIGNED' => 'ลงนามแล้ว',
        'ON_HOLD' => 'พักไว้ชั่วคราว',
        'CANCELLED' => 'ยกเลิก / ไม่ผ่าน',
    ];

    public const PRIORITIES = ['high' => 'สูง', 'medium' => 'ปานกลาง', 'low' => 'ต่ำ'];

    public function all(): array
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        if (! $batchId) {
            return [];
        }

        $records = DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->orderBy('id')->get(['source_id', 'payload', 'payload_sha256']);
        $states = DB::table('crm_pipeline_states')->whereIn('lead_source_id', $records->pluck('source_id'))
            ->get()->keyBy('lead_source_id');
        $sites = Site::query()->with('owner:id,name')
            ->whereIn('legacy_lead_id', $records->pluck('source_id'))->get()->keyBy('legacy_lead_id');
        $details = DB::table('crm_lead_details')->whereIn('lead_source_id', $records->pluck('source_id'))
            ->get(['lead_source_id', 'profile'])->keyBy('lead_source_id');
        $rows = [];

        foreach ($records as $record) {
            $source = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
            $currentProfile = $details->has($record->source_id)
                ? json_decode($details[$record->source_id]->profile, true, 512, JSON_THROW_ON_ERROR) : [];
            $site = $sites->get($record->source_id);
            $state = $states->get($record->source_id);
            $stage = $state?->stage ?? trim((string) ($source['stage'] ?? ''));
            $priority = trim((string) ($source['priority'] ?? ''));
            $probability = $state?->probability ?? ($source['probability'] ?? null);
            $validProbability = is_numeric($probability) && (float) $probability >= 0 && (float) $probability <= 100;
            $action = trim((string) (array_key_exists('next_action', $currentProfile)
                ? $currentProfile['next_action'] : ($source['next_action'] ?? '')));
            $actionAt = trim((string) (array_key_exists('next_action_at', $currentProfile)
                ? $currentProfile['next_action_at'] : ($source['next_action_at'] ?? '')));

            $rows[$record->source_id] = [
                'source_id' => $record->source_id,
                'site_id' => $site?->id,
                'site_code' => $site?->site_code,
                'site_name' => $site?->name ?? ($currentProfile['site_name'] ?? $source['site_name'] ?? __('pipeline_status.unnamed')),
                'area' => $site?->area ?? ($currentProfile['area'] ?? $source['area'] ?? null),
                'owner' => $site?->owner?->name ?? $site?->legacy_owner_label ?? ($currentProfile['assigned_to'] ?? $source['assigned_to'] ?? null) ?? __('pipeline_status.unassigned'),
                'stage_code' => $stage,
                'stage_origin' => $state === null ? 'legacy' : 'current',
                'stage_version' => $state === null
                    ? SalesStages::legacyVersion((int) $batchId, $record->payload_sha256)
                    : SalesStages::currentVersion((int) $state->version),
                'stage_label' => __('pipeline_status.stages')[$stage] ?? ($stage !== '' ? $stage : __('pipeline_status.unspecified')),
                'priority_code' => $priority,
                'priority_label' => __('pipeline_status.priorities')[$priority] ?? ($priority !== '' ? $priority : __('pipeline_status.unspecified')),
                'probability_display' => $probability === null || $probability === '' ? __('pipeline_status.unspecified') : (string) $probability.($validProbability ? '%' : ''),
                'probability_raw' => $probability,
                'next_action' => $action !== '' ? $action : __('pipeline_status.unspecified'),
                'next_action_raw' => $action,
                'next_action_at' => $actionAt !== '' && strtotime($actionAt) !== false ? $actionAt : __('pipeline_status.undated'),
                'next_action_at_raw' => $actionAt,
                'unmatched' => $site === null,
            ];
        }

        return $rows;
    }

    public function find(string $sourceId): ?array
    {
        return $this->all()[$sourceId] ?? null;
    }

    public function summary(): array
    {
        return $this->summaryFor($this->all());
    }

    public function summaryFor(array $rows): array
    {
        $groups = ['early' => 0, 'review_mou' => 0, 'contract_signed' => 0, 'paused_closed' => 0];
        $stages = array_fill_keys(array_keys(self::STAGES), 0);
        $unmatched = 0;

        foreach ($rows as $row) {
            $stage = $row['stage_code'];
            if ($stage !== '') {
                $stages[$stage] = ($stages[$stage] ?? 0) + 1;
            }
            if (in_array($stage, ['NEW', 'NEGOTIATING'], true)) {
                $groups['early']++;
            }
            if (in_array($stage, ['REVIEW', 'MOU'], true)) {
                $groups['review_mou']++;
            }
            if (in_array($stage, ['CONTRACT', 'SIGNED'], true)) {
                $groups['contract_signed']++;
            }
            if (in_array($stage, ['ON_HOLD', 'CANCELLED'], true)) {
                $groups['paused_closed']++;
            }
            if ($row['unmatched']) {
                $unmatched++;
            }
        }

        return ['total' => count($rows), 'groups' => $groups, 'stages' => $stages, 'unmatched' => $unmatched];
    }
}
