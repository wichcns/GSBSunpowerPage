<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use Illuminate\Support\Facades\DB;

class MyWork
{
    public function __construct(
        private readonly LegacyPipeline $pipeline,
        private readonly EngineeringProjects $engineering,
    ) {}

    public function forUser(int $userId): array
    {
        $leads = $this->pipeline->all();
        if ($leads === []) {
            return [];
        }

        $projects = array_column($this->engineering->all(), null, 'lead_source_id');
        $items = [];

        foreach (DB::table('crm_engineering_survey_tasks')
            ->where('assignee_user_id', $userId)->where('status', 'scheduled')->get() as $task) {
            $project = $projects[$task->lead_source_id] ?? null;
            if ($project === null || $project['engineering_stage'] !== 'AWAITING_SURVEY') {
                continue;
            }
            $items[] = $this->item('survey', $task->lead_source_id, $project['site_name'],
                __('my_work.task_types.survey'), $task->scheduled_at, __('my_work_extra.current_origin'),
                route('crm.admin.engineering.index', ['open' => $task->lead_source_id]));
        }

        foreach (DB::table('crm_engineering_construction_records')->where('owner_user_id', $userId)->get() as $record) {
            $project = $projects[$record->lead_source_id] ?? null;
            if ($project === null || $project['engineering_stage'] !== 'CONSTRUCTION') {
                continue;
            }
            $items[] = $this->item('construction', $record->lead_source_id, $project['site_name'],
                __('my_work.task_types.construction'), $record->target_finish_on, __('my_work_extra.current_origin'),
                route('crm.admin.engineering.index', ['open' => $record->lead_source_id]));
        }

        $sites = Site::query()->where('owner_user_id', $userId)->whereNotNull('legacy_lead_id')->get();
        $profiles = DB::table('crm_lead_details')->whereIn('lead_source_id', $sites->pluck('legacy_lead_id'))
            ->get(['lead_source_id', 'profile'])->keyBy('lead_source_id');
        foreach ($sites as $site) {
            $lead = $leads[$site->legacy_lead_id] ?? null;
            if ($lead === null || $lead['next_action_raw'] === '' || $lead['stage_code'] === 'CANCELLED') {
                continue;
            }
            $profile = $profiles->get($site->legacy_lead_id);
            $current = $profile !== null && array_key_exists('next_action', json_decode($profile->profile, true, 512, JSON_THROW_ON_ERROR));
            $items[] = $this->item('follow_up', $site->legacy_lead_id, $lead['site_name'],
                $lead['next_action_raw'], $lead['next_action_at_raw'],
                $current ? __('my_work_extra.current_origin') : __('my_work_extra.legacy_origin'),
                null);
        }

        usort($items, static fn (array $a, array $b): int => ($a['due_sort'] <=> $b['due_sort'])
            ?: strcmp($a['type'].'/'.$a['source_id'], $b['type'].'/'.$b['source_id']));

        return $items;
    }

    public function sitesForUser(int $userId): array
    {
        return Site::query()->where('owner_user_id', $userId)->orderBy('name')
            ->get(['id', 'site_code', 'name', 'area', 'legacy_lead_id'])
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'site_code' => $site->site_code,
                'name' => $site->name,
                'area' => $site->area,
                'source_id' => $site->legacy_lead_id,
                'url' => $site->legacy_lead_id === null ? null
                    : route('crm.admin.pipeline.index', ['open' => $site->legacy_lead_id]),
            ])->all();
    }

    private function item(string $type, string $sourceId, string $siteName, string $title,
        ?string $dueAt, string $origin, ?string $url): array
    {
        // Construction deadlines are date-only: the whole calendar day remains available.
        $timestamp = $dueAt ? strtotime($type === 'construction' ? $dueAt.' 23:59:59' : $dueAt) : false;

        return [
            'type' => $type,
            'source_id' => $sourceId,
            'site_name' => $siteName,
            'title' => $title,
            'due_at' => $timestamp === false ? null : $dueAt,
            'due_label' => $timestamp === false ? __('my_work.unspecified') : date($type === 'construction' ? 'd/m/Y' : 'd/m/Y H:i', $timestamp),
            'due_sort' => $timestamp === false ? PHP_INT_MAX : $timestamp,
            'overdue' => $timestamp !== false && $timestamp < now()->timestamp,
            'origin' => $origin,
            'url' => $url,
        ];
    }
}
