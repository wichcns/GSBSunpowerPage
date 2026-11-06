<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Support\EngineeringStages;
use App\Crm\Support\SalesStageGroups;
use Carbon\CarbonImmutable;

class ManagementReport
{
    public function __construct(
        private readonly LegacyPipeline $pipeline,
        private readonly LegacyContracts $contracts,
        private readonly EngineeringProjects $engineering,
    ) {}

    public function forUser(User $user, array $filters = []): array
    {
        $rows = $this->rowsForUser($user);
        $options = $this->options($rows);
        $rows = array_values(array_filter($rows, fn (array $row): bool => $this->matches($row, $filters)));
        $today = CarbonImmutable::now('Asia/Bangkok')->startOfDay();

        $sales = $this->stageCounts($rows, 'sales_stage', collect(SalesStageGroups::codes())->mapWithKeys(fn ($code) => [$code => __('sales_stage_groups.'.$code)])->all());
        $engineering = $this->stageCounts($rows, 'engineering_stage', collect(array_keys(EngineeringStages::LABELS))->mapWithKeys(fn ($code) => [$code => __('stages.engineering.'.$code)])->all());
        $workload = [];
        foreach ($rows as $row) {
            $key = $row['owner_name'] ?: __('reports.unassigned');
            $workload[$key] = ($workload[$key] ?? 0) + 1;
        }
        arsort($workload);
        $locations = [];
        foreach ($rows as $row) {
            $area = trim((string) ($row['area'] ?? ''));
            $area = $area === '' ? __('reports.unspecified_area') : $area;
            $locations[$area] = ($locations[$area] ?? 0) + 1;
        }
        ksort($locations, SORT_NATURAL | SORT_FLAG_CASE);

        $contract = ['complete' => 0, 'incomplete' => 0, 'next_90' => 0, 'expired' => 0];
        foreach ($rows as $row) {
            if ($row['contract_relevant']) {
                $contract[$row['contract_incomplete'] ? 'incomplete' : 'complete']++;
                if (isset($contract[$row['contract_expiry_class']])) {
                    $contract[$row['contract_expiry_class']]++;
                }
            }
        }

        $overdue = array_values(array_filter($rows, static function (array $row) use ($today): bool {
            if (! $row['next_action_at']) {
                return false;
            }
            try {
                return CarbonImmutable::parse($row['next_action_at'], 'Asia/Bangkok')->lessThan($today);
            } catch (\Throwable) {
                return false;
            }
        }));

        return [
            'rows' => $rows,
            'options' => $options,
            'filters' => $filters,
            'kpis' => [
                'sites' => count($rows),
                'active' => count(array_filter($rows, static fn (array $row): bool => ! in_array(SalesStageGroups::forStage($row['sales_stage']), ['SIGNED', 'CANCELLED'], true))),
                'signed' => $sales['SIGNED']['count'] ?? 0,
                'construction' => $engineering['CONSTRUCTION']['count'] ?? 0,
                'live' => $engineering['LIVE']['count'] ?? 0,
                'overdue' => count($overdue),
            ],
            'sales' => $sales,
            'engineering' => $engineering,
            'workload' => $workload,
            'locations' => $locations,
            'scope' => $user->role?->slug === 'manager' ? 'staff_all' : 'organization',
            'contract' => $contract,
            'attention' => [
                'overdue' => $overdue,
                'contracts' => array_values(array_filter($rows, static fn (array $row): bool => $row['contract_relevant'] && in_array($row['contract_expiry_class'], ['next_90', 'expired'], true))),
                'engineering_unassigned' => array_values(array_filter($rows, static fn (array $row): bool => $row['engineering_stage'] !== null && $row['owner_id'] === null)),
                'incomplete' => array_values(array_filter($rows, static fn (array $row): bool => $row['owner_id'] === null || $row['contacts_count'] === 0)),
            ],
        ];
    }

    private function rowsForUser(User $user): array
    {
        $pipeline = $this->pipeline->all();
        $contracts = $this->contracts->all();
        $projects = collect($this->engineering->all())->keyBy('lead_source_id');
        $sites = Site::query()->with(['owner.role', 'contacts'])->whereNotNull('legacy_lead_id')->get()->keyBy('legacy_lead_id');
        $manager = $user->role?->slug === 'manager';
        $rows = [];

        foreach ($pipeline as $sourceId => $lead) {
            $site = $sites->get($sourceId);
            if ($manager && (! $site?->owner || $site->owner->role?->slug !== 'staff')) {
                continue;
            }
            $project = $projects->get($sourceId);
            $agreement = $contracts[$sourceId] ?? null;
            $rows[] = [
                'source_id' => $sourceId,
                'site_code' => $site?->site_code,
                'site_name' => $lead['site_name'],
                'area' => $site?->area ?? $lead['area'],
                'location_type' => $site?->location_type,
                'owner_id' => $site?->owner_user_id,
                'owner_name' => $site?->owner?->name ?? $lead['owner'],
                'contacts_count' => $site?->contacts->count() ?? 0,
                'sales_stage' => $lead['stage_code'],
                'sales_stage_label' => $lead['display_stage_label'],
                'engineering_stage' => $project['engineering_stage'] ?? null,
                'engineering_stage_label' => isset($project['engineering_stage']) ? __('stages.engineering.'.$project['engineering_stage']) : __('dashboard.no_chart'),
                'next_action' => $lead['next_action_raw'],
                'next_action_at' => $lead['next_action_at_raw'],
                'contract_relevant' => $agreement !== null,
                'contract_incomplete' => $agreement ? $agreement['missing_fields'] !== [] : false,
                'contract_expiry' => $agreement['contract_expiry'] ?? null,
                'contract_expiry_class' => $agreement['expiry_class'] ?? null,
            ];
        }

        return $rows;
    }

    private function matches(array $row, array $filters): bool
    {
        if (($filters['owner'] ?? '') !== '' && (string) $row['owner_id'] !== (string) $filters['owner']) {
            return false;
        }
        if (($filters['area'] ?? '') !== '' && $row['area'] !== $filters['area']) {
            return false;
        }
        if (($filters['location_type'] ?? '') !== '' && $row['location_type'] !== $filters['location_type']) {
            return false;
        }
        if (($filters['sales_stage'] ?? '') !== '' && SalesStageGroups::forStage($row['sales_stage']) !== SalesStageGroups::forStage($filters['sales_stage'])) {
            return false;
        }
        if (($filters['engineering_stage'] ?? '') !== '' && $row['engineering_stage'] !== $filters['engineering_stage']) {
            return false;
        }
        $date = $row['next_action_at'] ? substr($row['next_action_at'], 0, 10) : null;
        if (($filters['date_from'] ?? '') !== '' && ($date === null || $date < $filters['date_from'])) {
            return false;
        }
        if (($filters['date_to'] ?? '') !== '' && ($date === null || $date > $filters['date_to'])) {
            return false;
        }

        return true;
    }

    private function options(array $rows): array
    {
        $pluck = static function (array $rows, string $key): array {
            $values = array_values(array_unique(array_filter(array_column($rows, $key), static fn ($value) => $value !== null && $value !== '')));
            natcasesort($values);

            return array_values($values);
        };
        $owners = [];
        foreach ($rows as $row) {
            if ($row['owner_id']) {
                $owners[$row['owner_id']] = $row['owner_name'];
            }
        }
        natcasesort($owners);

        return ['owners' => $owners, 'areas' => $pluck($rows, 'area'), 'location_types' => $pluck($rows, 'location_type')];
    }

    private function stageCounts(array $rows, string $key, array $labels): array
    {
        $result = [];
        foreach ($labels as $code => $label) {
            $result[$code] = ['label' => $label, 'count' => 0];
        }
        foreach ($rows as $row) {
            $code = $key === 'sales_stage'
                ? SalesStageGroups::forStage($row[$key] ?? null)
                : ($row[$key] ?? 'OTHER');
            if (! isset($result[$code])) {
                $result[$code] = ['label' => $code === 'OTHER' ? __('dashboard.other_status') : $code, 'count' => 0];
            }
            $result[$code]['count']++;
        }

        return $result;
    }
}
