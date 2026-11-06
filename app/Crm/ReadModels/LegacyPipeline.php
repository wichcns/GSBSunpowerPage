<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use App\Crm\Support\SalesStageGroups;
use App\Crm\Support\SalesStages;
use Illuminate\Support\Facades\DB;

class LegacyPipeline
{
    public const STAGES = [
        'NEW' => 'ลีดใหม่',
        'INTERNAL_REVIEW' => 'ตรวจสอบภายใน',
        'INTERNAL_APPROVED' => 'ผ่านการตรวจสอบภายใน',
        'MOU_SIGNING' => 'ขั้นตอนลงนาม MOU',
        'MOU_SIGNED' => 'MOU ลงนามแล้ว',
        'CONTRACT_SIGNING' => 'ขั้นตอนลงนามสัญญาความร่วมมือ',
        'CONTRACT_SIGNED' => 'สัญญาความร่วมมือลงนามแล้ว',
        'ON_HOLD' => 'พักไว้ชั่วคราว',
        'CANCELLED' => 'ยกเลิก / ไม่ผ่าน',
    ];

    public const PRIORITIES = ['high' => 'สูง', 'medium' => 'ปานกลาง', 'low' => 'ต่ำ'];

    public static function label(string $stage): string
    {
        $stages = __('pipeline_status.stages');
        $legacyStages = __('pipeline_status.legacy_stages');

        return $stages[$stage] ?? $legacyStages[$stage] ?? ($stage !== '' ? $stage : __('pipeline_status.unspecified'));
    }

    public function all(): array
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $records = $batchId === null ? collect() : DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->orderBy('id')->get(['source_id', 'payload', 'payload_sha256']);
        foreach ($records as $record) {
            $record->origin = 'legacy';
        }
        $native = DB::table('crm_native_opportunities')->orderBy('id')->get(['id', 'source_id', 'payload']);
        foreach ($native as $record) {
            $record->origin = 'native';
        }
        $records = $records->concat($native);
        $archived = DB::table('crm_archived_opportunities')->pluck('source_id')->flip();
        $records = $records->reject(fn ($record) => $archived->has($record->source_id));
        $states = DB::table('crm_pipeline_states')->whereIn('lead_source_id', $records->pluck('source_id'))
            ->get()->keyBy('lead_source_id');
        $sites = Site::query()->with('owner:id,name')
            ->whereIn('legacy_lead_id', $records->pluck('source_id'))->get()->keyBy('legacy_lead_id');
        $details = DB::table('crm_lead_details')->whereIn('lead_source_id', $records->pluck('source_id'))
            ->get(['lead_source_id', 'profile'])->keyBy('lead_source_id');
        $business = DB::table('crm_business_terms')->whereIn('lead_source_id', $records->pluck('source_id'))
            ->get(['lead_source_id', 'values'])->keyBy('lead_source_id');
        $rows = [];

        foreach ($records as $record) {
            $source = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
            $terms = $business->has($record->source_id)
                ? json_decode($business[$record->source_id]->values, true, 512, JSON_THROW_ON_ERROR) : [];
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
                'display_stage_code' => SalesStageGroups::forStage($stage),
                'display_stage_label' => SalesStageGroups::label($stage),
                'stage_origin' => $state === null ? $record->origin : 'current',
                'stage_version' => $state === null
                    ? ($record->origin === 'native'
                        ? SalesStages::nativeVersion((int) $record->id)
                        : SalesStages::legacyVersion((int) $batchId, $record->payload_sha256))
                    : SalesStages::currentVersion((int) $state->version),
                'stage_label' => self::label($stage),
                'priority_code' => $priority,
                'priority_label' => __('pipeline_status.priorities')[$priority] ?? ($priority !== '' ? $priority : __('pipeline_status.unspecified')),
                'probability_display' => $probability === null || $probability === '' ? __('pipeline_status.unspecified') : (string) $probability.($validProbability ? '%' : ''),
                'probability_raw' => $probability,
                'commercial_model' => $terms['commercial_model'] ?? $source['commercial_model'] ?? null,
                'rental_cost' => $terms['rental_cost'] ?? $source['rental_cost'] ?? null,
                'rental_unit' => $terms['rental_unit'] ?? $source['rental_unit'] ?? null,
                'parking_spaces' => $terms['parking_spaces'] ?? $source['parking_spaces'] ?? null,
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
        $displayStages = array_fill_keys(SalesStageGroups::codes(), 0);
        $unmatched = 0;

        foreach ($rows as $row) {
            $stage = $row['stage_code'];
            if ($stage !== '') {
                $stages[$stage] = ($stages[$stage] ?? 0) + 1;
            }
            $displayStage = SalesStageGroups::forStage($stage);
            $displayStages[$displayStage] = ($displayStages[$displayStage] ?? 0) + 1;
            if (in_array($displayStage, ['NEW', 'NEGOTIATING'], true)) {
                $groups['early']++;
            }
            if (in_array($displayStage, ['REVIEW', 'MOU'], true)) {
                $groups['review_mou']++;
            }
            if (in_array($displayStage, ['CONTRACT', 'SIGNED'], true)) {
                $groups['contract_signed']++;
            }
            if (in_array($displayStage, ['ON_HOLD', 'CANCELLED'], true)) {
                $groups['paused_closed']++;
            }
            if ($row['unmatched']) {
                $unmatched++;
            }
        }

        return ['total' => count($rows), 'groups' => $groups, 'stages' => $stages, 'display_stages' => $displayStages, 'unmatched' => $unmatched];
    }
}
