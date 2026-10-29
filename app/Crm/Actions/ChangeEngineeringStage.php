<?php

namespace App\Crm\Actions;

use App\Crm\ReadModels\EngineeringProjects;
use App\Crm\Services\CrmLineNotifier;
use App\Crm\Support\EngineeringStages;
use App\Crm\Support\SalesStages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChangeEngineeringStage
{
    public function __construct(
        private readonly EngineeringProjects $projects,
        private readonly SyncEngineeringSurveyTask $surveys,
        private readonly CrmLineNotifier $notifier,
    ) {}

    public function execute(string $sourceId, string $stage, string $expectedVersion, int $actorId, bool $notify = true): array
    {
        abort_unless(in_array($stage, EngineeringStages::allowed(), true), 422, 'ขั้นวิศวกรรมไม่ถูกต้อง');

        return DB::transaction(function () use ($sourceId, $stage, $expectedVersion, $actorId, $notify): array {
            $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
            $lead = $batchId === null ? null : DB::table('crm_legacy_records')
                ->where('batch_id', $batchId)->where('source_table', 'leads')
                ->where('source_id', $sourceId)->lockForUpdate()->first();
            abort_if($lead === null, 404);

            $legacy = DB::table('crm_legacy_records')->where('batch_id', $batchId)
                ->where('source_table', 'projects')->get(['payload', 'payload_sha256']);
            $matching = $legacy->filter(function ($record) use ($sourceId) {
                $project = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);

                return (string) ($project['lead_id'] ?? '') === $sourceId;
            })->values();
            abort_if($matching->count() > 1, 409, 'พบโครงการเดิมหลายรายการ กรุณาตรวจสอบข้อมูล');

            $current = DB::table('crm_engineering_projects')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            abort_if($current === null && $matching->isEmpty(), 404);
            $version = $current === null
                ? "legacy:{$batchId}:{$matching[0]->payload_sha256}"
                : "current:{$batchId}:{$current->version}";
            abort_if($version !== $expectedVersion, 409, 'ข้อมูลเปลี่ยนไปแล้ว กรุณาโหลดข้อมูลล่าสุด');

            $legacyProject = $matching->isEmpty() ? null : json_decode($matching[0]->payload, true, 512, JSON_THROW_ON_ERROR);
            $oldStage = $current?->engineering_stage ?? ($legacyProject['engineering_stage'] ?? 'NOT_STARTED');
            if ($oldStage === $stage) {
                return ['project' => $this->projects->find($sourceId), 'sales_stage' => null];
            }

            if ($current === null) {
                $projectId = DB::table('crm_engineering_projects')->insertGetId([
                    'lead_source_id' => $sourceId,
                    'project_code' => $legacyProject['project_code'] ?? 'GSB-CRM-'.Str::ulid(),
                    'legacy_project_id' => $legacyProject['id'] ?? null,
                    'engineering_stage' => $stage,
                    'sync_status' => 'pending_connection',
                    'external_project_id' => $legacyProject['external_project_id'] ?? null,
                    'origin' => 'legacy', 'version' => 1, 'updated_by_user_id' => $actorId,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                $projectId = $current->id;
                DB::table('crm_engineering_projects')->where('id', $projectId)->update([
                    'engineering_stage' => $stage, 'sync_status' => 'pending_connection',
                    'version' => $current->version + 1, 'updated_by_user_id' => $actorId,
                    'updated_at' => now(),
                ]);
            }

            $historyId = DB::table('crm_engineering_stage_histories')->insertGetId([
                'engineering_project_id' => $projectId, 'lead_source_id' => $sourceId,
                'from_stage' => $oldStage, 'to_stage' => $stage,
                'actor_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);

            $source = json_decode($lead->payload, true, 512, JSON_THROW_ON_ERROR);
            if ($notify) {
                $this->notifier->engineeringStage($sourceId, (string) ($source['site_name'] ?? 'ไม่ระบุชื่อ'), $oldStage, $stage, $actorId, $historyId);
            }

            $this->surveys->afterStageChange($projectId, $sourceId, $oldStage, $stage, $actorId);

            $salesStage = null;
            if (in_array($stage, ['CONSTRUCTION', 'LIVE'], true)) {
                $state = DB::table('crm_pipeline_states')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
                $oldSalesStage = $state?->stage ?? trim((string) ($source['stage'] ?? ''));
                $salesStage = 'SIGNED';
                if ($oldSalesStage !== 'SIGNED') {
                    $oldProbability = $state?->probability ?? ($source['probability'] ?? null);
                    if ($state === null) {
                        DB::table('crm_pipeline_states')->insert([
                            'lead_source_id' => $sourceId, 'stage' => 'SIGNED', 'probability' => 100,
                            'version' => 1, 'updated_by_user_id' => $actorId,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('crm_pipeline_states')->where('id', $state->id)->update([
                            'stage' => 'SIGNED', 'probability' => 100,
                            'version' => $state->version + 1, 'updated_by_user_id' => $actorId,
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('crm_stage_histories')->insert([
                        'lead_source_id' => $sourceId, 'from_stage' => $oldSalesStage ?: null,
                        'to_stage' => 'SIGNED',
                        'from_probability' => is_numeric($oldProbability) && $oldProbability >= 0 && $oldProbability <= 100 ? (int) $oldProbability : null,
                        'to_probability' => SalesStages::probability('SIGNED'),
                        'actor_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }

            DB::table('crm_integration_outbox')->insert([
                'engineering_project_id' => $projectId,
                'event_type' => 'crm.project.engineering_stage_changed',
                'dedupe_key' => 'crm.project.engineering_stage_changed:'.$projectId.':'.($current?->version ?? 0).':'.$stage,
                'status' => 'pending_connection',
                'payload' => json_encode(['lead_source_id' => $sourceId, 'engineering_stage' => $stage], JSON_THROW_ON_ERROR),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return ['project' => $this->projects->find($sourceId), 'sales_stage' => $salesStage];
        });
    }
}
