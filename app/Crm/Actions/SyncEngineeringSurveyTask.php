<?php

namespace App\Crm\Actions;

use Illuminate\Support\Facades\DB;

class SyncEngineeringSurveyTask
{
    public function afterStageChange(int $projectId, string $sourceId, string $oldStage, string $newStage, int $actorId): void
    {
        if ($oldStage === $newStage) {
            return;
        }

        $task = DB::table('crm_engineering_survey_tasks')->where('engineering_project_id', $projectId)
            ->lockForUpdate()->first();
        if ($newStage === 'AWAITING_SURVEY') {
            if ($task === null) {
                $taskId = DB::table('crm_engineering_survey_tasks')->insertGetId([
                    'engineering_project_id' => $projectId, 'lead_source_id' => $sourceId,
                    'status' => 'unassigned', 'version' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->record($taskId, 'created', null, ['status' => 'unassigned'], $actorId);
            } else {
                $before = $this->snapshot($task);
                DB::table('crm_engineering_survey_tasks')->where('id', $task->id)->update([
                    'status' => 'unassigned', 'assignee_user_id' => null, 'scheduled_at' => null,
                    'result_summary' => null, 'completed_by_user_id' => null, 'completed_at' => null,
                    'version' => $task->version + 1, 'updated_at' => now(),
                ]);
                $this->record($task->id, 'reopened', $before, ['status' => 'unassigned'], $actorId);
            }

            return;
        }

        if ($oldStage === 'AWAITING_SURVEY' && $task !== null && in_array($task->status, ['unassigned', 'scheduled'], true)) {
            DB::table('crm_engineering_survey_tasks')->where('id', $task->id)->update([
                'status' => 'cancelled', 'version' => $task->version + 1, 'updated_at' => now(),
            ]);
            $this->record($task->id, 'cancelled', $this->snapshot($task), ['status' => 'cancelled'], $actorId);
        }
    }

    private function snapshot(object $task): array
    {
        return [
            'status' => $task->status, 'assignee_user_id' => $task->assignee_user_id,
            'scheduled_at' => $task->scheduled_at, 'result_summary' => $task->result_summary,
            'completed_at' => $task->completed_at,
        ];
    }

    private function record(int $taskId, string $event, ?array $old, array $new, int $actorId): void
    {
        DB::table('crm_engineering_survey_histories')->insert([
            'survey_task_id' => $taskId, 'event_code' => $event,
            'old_values' => $old === null ? null : json_encode($old, JSON_THROW_ON_ERROR),
            'new_values' => json_encode($new, JSON_THROW_ON_ERROR),
            'actor_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
