<?php

namespace App\Crm\Actions;

use App\Crm\ReadModels\EngineeringProjects;
use App\Crm\ReadModels\EngineeringSurveyQueue;
use Illuminate\Support\Facades\DB;

class SaveEngineeringSurvey
{
    public function __construct(
        private readonly EngineeringProjects $projects,
        private readonly EngineeringSurveyQueue $surveys,
        private readonly EnsureEngineeringProject $ensureProject,
        private readonly ChangeEngineeringStage $changeStage,
    ) {}

    public function schedule(string $sourceId, ?int $assigneeId, ?string $scheduledAt, string $expectedVersion, int $actorId): array
    {
        abort_if(($assigneeId === null) !== ($scheduledAt === null), 422, 'กรุณาระบุทั้งผู้รับผิดชอบและวันนัดสำรวจ');

        return DB::transaction(function () use ($sourceId, $assigneeId, $scheduledAt, $expectedVersion, $actorId): array {
            $batchId = $this->lockLead($sourceId);
            $project = $this->projects->find($sourceId);
            abort_if($project === null, 404);
            abort_if($project['engineering_stage'] !== 'AWAITING_SURVEY', 409, 'โครงการไม่ได้อยู่ขั้นรอสำรวจ');
            $visible = $this->surveys->forProject($sourceId);
            abort_if($visible['version'] !== $expectedVersion, 409, 'งานเปลี่ยนไปแล้ว กรุณาโหลดข้อมูลล่าสุด');
            $task = DB::table('crm_engineering_survey_tasks')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            abort_if($task !== null && ! in_array($task->status, ['unassigned', 'scheduled'], true), 409);

            if ($task === null && $assigneeId === null) {
                return $this->result($sourceId);
            }
            if ($task !== null && (int) ($task->assignee_user_id ?? 0) === (int) ($assigneeId ?? 0)
                && $task->scheduled_at === $scheduledAt) {
                return $this->result($sourceId);
            }

            $currentProject = $this->ensureProject->forLead($sourceId, $batchId);
            $status = $assigneeId === null ? 'unassigned' : 'scheduled';
            if ($task === null) {
                $taskId = DB::table('crm_engineering_survey_tasks')->insertGetId([
                    'engineering_project_id' => $currentProject['id'], 'lead_source_id' => $sourceId,
                    'status' => $status, 'assignee_user_id' => $assigneeId,
                    'scheduled_at' => $scheduledAt, 'version' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $old = null;
            } else {
                $taskId = $task->id;
                $old = $this->snapshot($task);
                DB::table('crm_engineering_survey_tasks')->where('id', $taskId)->update([
                    'status' => $status, 'assignee_user_id' => $assigneeId,
                    'scheduled_at' => $scheduledAt, 'version' => $task->version + 1,
                    'updated_at' => now(),
                ]);
            }
            $this->history($taskId, 'scheduled', $old, [
                'status' => $status, 'assignee_user_id' => $assigneeId, 'scheduled_at' => $scheduledAt,
            ], $actorId);

            return $this->result($sourceId);
        });
    }

    public function complete(string $sourceId, string $resultSummary, string $expectedVersion, string $expectedProjectVersion, int $actorId): array
    {
        return DB::transaction(function () use ($sourceId, $resultSummary, $expectedVersion, $expectedProjectVersion, $actorId): array {
            $this->lockLead($sourceId);
            $project = $this->projects->find($sourceId);
            abort_if($project === null, 404);
            abort_if($project['engineering_stage'] !== 'AWAITING_SURVEY', 409, 'โครงการไม่ได้อยู่ขั้นรอสำรวจ');
            abort_if($project['version'] !== $expectedProjectVersion, 409, 'โครงการเปลี่ยนไปแล้ว กรุณาโหลดข้อมูลล่าสุด');
            $task = DB::table('crm_engineering_survey_tasks')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            abort_if($task === null || $task->status !== 'scheduled' || $task->assignee_user_id === null || $task->scheduled_at === null, 422, 'กรุณามอบหมายและนัดหมายก่อนปิดงาน');
            abort_if('current:'.$task->version !== $expectedVersion, 409, 'งานเปลี่ยนไปแล้ว กรุณาโหลดข้อมูลล่าสุด');

            $old = $this->snapshot($task);
            DB::table('crm_engineering_survey_tasks')->where('id', $task->id)->update([
                'status' => 'done', 'result_summary' => trim($resultSummary),
                'completed_by_user_id' => $actorId, 'completed_at' => now(),
                'version' => $task->version + 1, 'updated_at' => now(),
            ]);
            $this->history($task->id, 'completed', $old, [
                'status' => 'done', 'result_summary' => trim($resultSummary),
                'completed_by_user_id' => $actorId,
            ], $actorId);
            $this->changeStage->execute($sourceId, 'SURVEYED', $expectedProjectVersion, $actorId);

            return $this->result($sourceId);
        });
    }

    private function lockLead(string $sourceId): int
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $lead = $batchId === null ? null : DB::table('crm_legacy_records')
            ->where('batch_id', $batchId)->where('source_table', 'leads')
            ->where('source_id', $sourceId)->lockForUpdate()->first();
        abort_if($lead === null, 404);

        return (int) $batchId;
    }

    private function snapshot(object $task): array
    {
        return [
            'status' => $task->status, 'assignee_user_id' => $task->assignee_user_id,
            'scheduled_at' => $task->scheduled_at, 'result_summary' => $task->result_summary,
            'completed_at' => $task->completed_at,
        ];
    }

    private function history(int $taskId, string $event, ?array $old, array $new, int $actorId): void
    {
        DB::table('crm_engineering_survey_histories')->insert([
            'survey_task_id' => $taskId, 'event_code' => $event,
            'old_values' => $old === null ? null : json_encode($old, JSON_THROW_ON_ERROR),
            'new_values' => json_encode($new, JSON_THROW_ON_ERROR),
            'actor_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function result(string $sourceId): array
    {
        return ['project' => $this->projects->find($sourceId), 'survey_task' => $this->surveys->forProject($sourceId)];
    }
}
