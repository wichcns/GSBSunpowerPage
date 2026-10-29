<?php

namespace App\Crm\ReadModels;

use Illuminate\Support\Facades\DB;

class EngineeringSurveyQueue
{
    public function __construct(private readonly EngineeringProjects $projects) {}

    public function all(): array
    {
        $projects = $this->projects->all();
        $tasks = DB::table('crm_engineering_survey_tasks')
            ->whereIn('lead_source_id', array_column($projects, 'lead_source_id'))
            ->get()->keyBy('lead_source_id');
        $users = DB::table('crm_users')->whereIn('id', $tasks->pluck('assignee_user_id')->filter())
            ->get(['id', 'name'])->keyBy('id');
        $rows = [];

        foreach ($projects as $project) {
            $task = $tasks->get($project['lead_source_id']);
            if ($project['engineering_stage'] !== 'AWAITING_SURVEY' || ($task && ! in_array($task->status, ['unassigned', 'scheduled'], true))) {
                continue;
            }
            $rows[] = $this->row($project, $task, $users);
        }

        return $rows;
    }

    public function forProject(string $leadSourceId): ?array
    {
        $project = $this->projects->find($leadSourceId);
        if ($project === null) {
            return null;
        }

        $task = DB::table('crm_engineering_survey_tasks')->where('lead_source_id', $leadSourceId)->first();
        if ($task === null && $project['engineering_stage'] !== 'AWAITING_SURVEY') {
            return null;
        }

        $users = $task?->assignee_user_id
            ? DB::table('crm_users')->where('id', $task->assignee_user_id)->get(['id', 'name'])->keyBy('id')
            : collect();

        return $this->row($project, $task, $users);
    }

    private function row(array $project, ?object $task, $users): array
    {
        return [
            'lead_source_id' => $project['lead_source_id'],
            'site_name' => $project['site_name'],
            'project_code' => $project['project_code'],
            'status' => $task?->status ?? 'unassigned',
            'assignee_user_id' => $task?->assignee_user_id,
            'assignee_name' => $task?->assignee_user_id ? $users->get($task->assignee_user_id)?->name : null,
            'scheduled_at' => $task?->scheduled_at,
            'result_summary' => $task?->result_summary,
            'completed_at' => $task?->completed_at,
            'version' => $task ? 'current:'.$task->version : 'virtual:'.$project['version'],
        ];
    }
}
