<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Support\AssignedCrmRecords;
use App\Crm\Support\EngineeringStages;
use Illuminate\Support\Facades\DB;

class DashboardOverview
{
    public function __construct(
        private readonly MyWork $myWork,
        private readonly TeamWork $teamWork,
        private readonly LegacyPipeline $pipeline,
        private readonly LegacyContracts $contracts,
        private readonly EngineeringProjects $engineering,
        private readonly AssignedCrmRecords $assigned,
    ) {}

    public function forUser(User $user): array
    {
        $scope = match ($user->role?->slug) {
            'super_admin', 'admin' => 'organization',
            'manager' => 'staff_all',
            default => 'personal',
        };

        $content = match ($scope) {
            'organization' => $this->organization($user),
            'staff_all' => $this->staffAll($user),
            default => $this->personal($user),
        };

        return [
            'scope' => $scope,
            ...$content,
            'charts' => $this->chartsForUser($user, $scope),
            'updated_at' => DB::table('crm_legacy_import_batches')->where('status', 'completed')
                ->latest('id')->value('exported_at'),
        ];
    }

    private function personal(User $user): array
    {
        $workUrl = $user->hasPermission('work.view') ? route('crm.admin.my-work') : null;
        $items = $workUrl ? $this->myWork->forUser($user->id) : [];
        $sites = $workUrl ? $this->myWork->sitesForUser($user->id) : [];
        $dueSoon = now()->addDays(7)->timestamp;
        $attention = array_slice(array_map(static fn (array $item): array => [
            'title' => $item['title'],
            'site_name' => $item['site_name'],
            'detail' => __('dashboard.due_label', ['date' => $item['due_label']]),
            'url' => $item['type'] === 'follow_up'
                ? route('crm.admin.my-work') : $item['url'],
            'tone' => $item['overdue'] ? 'urgent' : 'normal',
        ], array_values(array_filter($items, static fn (array $item): bool => $item['due_at'] !== null && $item['due_sort'] <= $dueSoon))), 0, 5);

        return [
            'cards' => [
                'sites' => $this->card(__('dashboard.cards.assigned_sites'), count($sites), $workUrl),
                'tasks' => $this->card(__('dashboard.cards.all_tasks'), count($items), $workUrl),
                'overdue' => $this->card(__('dashboard.cards.overdue'), count(array_filter($items, static fn (array $item): bool => $item['overdue'])), $workUrl),
                'undated' => $this->card(__('dashboard.cards.undated'), count(array_filter($items, static fn (array $item): bool => $item['due_at'] === null)), $workUrl),
            ],
            'attention' => $attention,
            'primary_url' => $workUrl,
        ];
    }

    private function staffAll(User $user): array
    {
        $teamUrl = $user->hasPermission('team.view') ? route('crm.admin.team-work') : null;
        $staff = $teamUrl ? $this->teamWork->forStaff() : [];
        $attention = array_slice(array_map(static fn (array $person): array => [
            'title' => $person['user']->name,
            'site_name' => 'Staff',
            'detail' => __('dashboard.overdue_count', ['count' => $person['overdue']]),
            'url' => route('crm.admin.team-work', ['staff' => $person['user']->id]),
            'tone' => 'urgent',
        ], array_values(array_filter($this->sortStaffByOverdue($staff), static fn (array $person): bool => $person['overdue'] > 0))), 0, 5);

        return [
            'cards' => [
                'staff' => $this->card(__('dashboard.cards.staff'), count($staff), $teamUrl),
                'sites' => $this->card(__('dashboard.cards.staff_sites'), array_sum(array_map(static fn (array $person): int => count($person['sites']), $staff)), $teamUrl),
                'tasks' => $this->card(__('dashboard.cards.staff_tasks'), array_sum(array_map(static fn (array $person): int => count($person['items']), $staff)), $teamUrl),
                'overdue' => $this->card(__('dashboard.cards.overdue'), array_sum(array_column($staff, 'overdue')), $teamUrl),
            ],
            'attention' => $attention,
            'primary_url' => $teamUrl,
        ];
    }

    private function organization(User $user): array
    {
        $siteUrl = $user->hasPermission('sites.view') ? route('crm.admin.pipeline.index', ['section' => 'sites']) : null;
        $pipelineUrl = $user->hasPermission('pipeline.view') ? route('crm.admin.pipeline.index') : null;
        $contractUrl = $user->hasPermission('contracts.view') ? route('crm.admin.contracts.index') : null;
        $engineeringUrl = $user->hasPermission('engineering.view') ? route('crm.admin.engineering.index') : null;
        $leads = $pipelineUrl ? $this->pipeline->all() : [];
        $contracts = $contractUrl ? $this->contracts->all() : [];
        $projects = $engineeringUrl ? $this->engineering->all() : [];
        $staff = $user->hasPermission('team.view') ? $this->teamWork->forStaff() : [];
        $attention = [];
        foreach ($contracts as $row) {
            if ($row['missing_fields'] === []) {
                continue;
            }
            $attention[] = [
                'title' => $row['site_name'],
                'site_name' => __('nav.contracts'),
                'detail' => __('dashboard.missing_fields', ['fields' => implode(', ', $row['missing_fields'])]),
                'url' => route('crm.admin.contracts.index', ['information' => 'missing']),
                'tone' => 'urgent',
            ];
            if (count($attention) === 3) {
                break;
            }
        }
        foreach ($this->sortStaffByOverdue($staff) as $person) {
            if ($person['overdue'] === 0 || count($attention) >= 5) {
                break;
            }
            $attention[] = [
                'title' => $person['user']->name,
                'site_name' => __('nav.team_work'),
                'detail' => __('dashboard.overdue_count', ['count' => $person['overdue']]),
                'url' => route('crm.admin.team-work', ['staff' => $person['user']->id]),
                'tone' => 'urgent',
            ];
        }

        return [
            'cards' => [
                'sites' => $this->card(__('dashboard.cards.sites'), $siteUrl ? Site::query()->count() : 0, $siteUrl),
                'opportunities' => $this->card(__('dashboard.cards.opportunities'), count($leads), $pipelineUrl),
                'contracts' => $this->card(__('dashboard.cards.contracts'), count($contracts), $contractUrl),
                'engineering' => $this->card(__('dashboard.cards.engineering'), count($projects), $engineeringUrl),
            ],
            'attention' => $attention,
            'primary_url' => $pipelineUrl,
        ];
    }

    private function card(string $label, int $value, ?string $url): array
    {
        return ['label' => $label, 'value' => $value, 'url' => $url];
    }

    private function chartsForUser(User $user, string $scope): array
    {
        $salesAllowed = $user->hasPermission('pipeline.view');
        $engineeringAllowed = $user->hasPermission('engineering.view');
        if (! $salesAllowed && ! $engineeringAllowed) {
            return [];
        }

        $salesIds = null;
        $engineeringIds = null;
        if ($scope === 'personal') {
            $salesIds = $this->assigned->pipeline($user);
            $engineeringIds = $this->assigned->engineering($user);
        } elseif ($scope === 'staff_all') {
            $salesIds = [];
            $engineeringIds = [];
            if ($user->hasPermission('team.view')) {
                $staff = User::query()->whereHas('role', fn ($query) => $query->where('slug', 'staff'))->get();
                foreach ($staff as $person) {
                    array_push($salesIds, ...$this->assigned->pipeline($person));
                    array_push($engineeringIds, ...$this->assigned->engineering($person));
                }
            }
        }

        $charts = [];
        if ($salesAllowed) {
            $leads = $this->pipeline->all();
            if ($salesIds !== null) {
                $leads = array_intersect_key($leads, array_flip($salesIds));
            }
            $salesLabels = collect(array_keys(LegacyPipeline::STAGES))->mapWithKeys(fn (string $code): array => [$code => __('stages.sales.'.$code)])->all();
            $charts['sales'] = $this->stageChart($leads, 'stage_code', $salesLabels,
                'CONTRACT_SIGNED', __('dashboard.signed'), $scope === 'staff_all' ? route('crm.admin.team-work') : route('crm.admin.pipeline.index'),
                $scope !== 'staff_all');
        }
        if ($engineeringAllowed) {
            $projects = $this->engineering->all();
            if ($engineeringIds !== null) {
                $allowed = array_flip($engineeringIds);
                $projects = array_values(array_filter($projects,
                    static fn (array $row): bool => isset($allowed[$row['lead_source_id']])));
            }
            $engineeringLabels = collect(array_keys(EngineeringStages::LABELS))->mapWithKeys(fn (string $code): array => [$code => __('stages.engineering.'.$code)])->all();
            $charts['engineering'] = $this->stageChart($projects, 'engineering_stage', $engineeringLabels,
                'LIVE', __('dashboard.live'), route('crm.admin.engineering.index'), false);
        }

        return $charts;
    }

    private function stageChart(array $rows, string $stageKey, array $labels,
        string $successCode, string $successLabel, string $url, bool $filterable): array
    {
        $counts = array_fill_keys(array_keys($labels), 0);
        foreach ($rows as $row) {
            $code = $row[$stageKey] ?? '';
            $counts[isset($labels[$code]) ? $code : 'OTHER'] = ($counts[isset($labels[$code]) ? $code : 'OTHER'] ?? 0) + 1;
        }
        $total = count($rows);
        $stages = [];
        foreach ($counts as $code => $count) {
            $stages[$code] = [
                'label' => $labels[$code] ?? __('dashboard.other_status'),
                'count' => $count,
                'percent' => $total ? (int) round($count * 100 / $total) : 0,
                'url' => $filterable && $code !== 'OTHER' ? route('crm.admin.pipeline.index', ['stage' => $code]) : $url,
            ];
        }

        return [
            'total' => $total,
            'success' => $counts[$successCode] ?? 0,
            'success_label' => $successLabel,
            'percent' => $total ? (int) round(($counts[$successCode] ?? 0) * 100 / $total) : 0,
            'stages' => $stages,
        ];
    }

    private function sortStaffByOverdue(array $staff): array
    {
        usort($staff, static fn (array $a, array $b): int => $b['overdue'] <=> $a['overdue']
            ?: strcasecmp($a['user']->name, $b['user']->name));

        return $staff;
    }
}
