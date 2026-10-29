<?php

namespace App\Crm\Actions;

use App\Crm\ReadModels\EngineeringConstruction;
use App\Crm\ReadModels\EngineeringProjects;
use App\Crm\Services\CrmLineNotifier;
use Illuminate\Support\Facades\DB;

class ManageEngineeringConstruction
{
    public function __construct(
        private readonly EngineeringProjects $projects,
        private readonly EngineeringConstruction $construction,
        private readonly ChangeEngineeringStage $changeStage,
        private readonly EnsureEngineeringProject $ensureProject,
        private readonly CrmLineNotifier $notifier,
    ) {}

    public function start(string $sourceId, array $data, int $actorId): array
    {
        return DB::transaction(function () use ($sourceId, $data, $actorId): array {
            $this->lockLead($sourceId);
            $project = $this->projects->find($sourceId);
            abort_if($project === null, 404);
            abort_if($project['version'] !== $data['expected_project_version'], 409, 'โครงการเปลี่ยนไปแล้ว กรุณาโหลดข้อมูลล่าสุด');
            abort_if($project['engineering_stage'] !== 'SURVEYED', 409, 'ต้องสำรวจแล้วก่อนเริ่มก่อสร้าง');

            $this->changeStage->execute($sourceId, 'CONSTRUCTION', $project['version'], $actorId, false);
            $current = DB::table('crm_engineering_projects')->where('lead_source_id', $sourceId)->first();
            $record = DB::table('crm_engineering_construction_records')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            $values = [
                'owner_user_id' => $data['owner_user_id'], 'started_on' => $data['started_on'],
                'target_finish_on' => $data['target_finish_on'] ?? null,
                'updated_by_user_id' => $actorId, 'updated_at' => now(),
            ];
            if ($record === null) {
                $id = DB::table('crm_engineering_construction_records')->insertGetId([
                    ...$values, 'engineering_project_id' => $current->id, 'lead_source_id' => $sourceId,
                    'version' => 1, 'created_at' => now(),
                ]);
            } else {
                $id = $record->id;
                DB::table('crm_engineering_construction_records')->where('id', $id)->update([...$values, 'version' => $record->version + 1]);
            }
            if (trim((string) ($data['note'] ?? '')) !== '') {
                $this->append($id, trim($data['note']), $actorId);
            }
            $this->notifier->constructionStarted(
                $sourceId,
                (string) ($project['site_name'] ?? 'ไม่ระบุชื่อ'),
                (int) $data['owner_user_id'],
                $data['started_on'],
                $data['target_finish_on'] ?? null,
                $actorId,
                $id,
            );

            return $this->result($sourceId);
        });
    }

    public function saveDetails(string $sourceId, array $data, int $actorId): array
    {
        return DB::transaction(function () use ($sourceId, $data, $actorId): array {
            $batchId = $this->lockLead($sourceId);
            $project = $this->projects->find($sourceId);
            abort_if($project === null, 404);
            abort_if($project['engineering_stage'] !== 'CONSTRUCTION', 409, 'โครงการไม่ได้อยู่ขั้นกำลังก่อสร้าง');
            abort_if($project['version'] !== $data['expected_project_version'], 409, 'โครงการเปลี่ยนไปแล้ว กรุณาโหลดข้อมูลล่าสุด');
            $record = DB::table('crm_engineering_construction_records')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            abort_if(($record === null ? null : 'current:'.$record->version) !== ($data['expected_version'] ?? null), 409, 'ข้อมูลงานก่อสร้างเปลี่ยนไปแล้ว');
            $values = [
                'owner_user_id' => $data['owner_user_id'], 'started_on' => $data['started_on'],
                'target_finish_on' => $data['target_finish_on'] ?? null,
                'updated_by_user_id' => $actorId, 'updated_at' => now(),
            ];
            if ($record === null) {
                $current = $this->ensureProject->forLead($sourceId, $batchId);
                DB::table('crm_engineering_construction_records')->insert([
                    ...$values, 'engineering_project_id' => $current['id'], 'lead_source_id' => $sourceId,
                    'version' => 1, 'created_at' => now(),
                ]);
            } elseif ((int) $record->owner_user_id !== (int) $data['owner_user_id']
                || $record->started_on !== $data['started_on']
                || $record->target_finish_on !== ($data['target_finish_on'] ?? null)) {
                DB::table('crm_engineering_construction_records')->where('id', $record->id)->update([...$values, 'version' => $record->version + 1]);
            }

            return $this->result($sourceId);
        });
    }

    public function addUpdate(string $sourceId, string $note, string $expectedVersion, int $actorId): array
    {
        return DB::transaction(function () use ($sourceId, $note, $expectedVersion, $actorId): array {
            $this->lockLead($sourceId);
            $project = $this->projects->find($sourceId);
            abort_if($project === null, 404);
            abort_if($project['engineering_stage'] !== 'CONSTRUCTION', 409, 'โครงการไม่ได้อยู่ขั้นกำลังก่อสร้าง');
            $record = DB::table('crm_engineering_construction_records')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            abort_if($record === null, 422, 'กรุณาบันทึกข้อมูลก่อสร้างก่อน');
            abort_if('current:'.$record->version !== $expectedVersion, 409, 'ข้อมูลงานก่อสร้างเปลี่ยนไปแล้ว');
            $updateId = $this->append($record->id, trim($note), $actorId);
            DB::table('crm_engineering_construction_records')->where('id', $record->id)->update([
                'version' => $record->version + 1, 'updated_by_user_id' => $actorId, 'updated_at' => now(),
            ]);
            $this->notifier->constructionProgress(
                $sourceId,
                (string) ($project['site_name'] ?? 'ไม่ระบุชื่อ'),
                (int) $record->owner_user_id,
                trim($note),
                $actorId,
                $updateId,
            );

            return $this->result($sourceId);
        });
    }

    private function lockLead(string $sourceId): int
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $lead = $batchId === null ? null : DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->where('source_id', $sourceId)->lockForUpdate()->first();
        abort_if($lead === null, 404);

        return (int) $batchId;
    }

    private function append(int $recordId, string $note, int $actorId): int
    {
        return DB::table('crm_engineering_construction_updates')->insertGetId([
            'construction_record_id' => $recordId, 'note' => $note,
            'actor_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function result(string $sourceId): array
    {
        return ['project' => $this->projects->find($sourceId), 'construction' => $this->construction->forProject($sourceId)];
    }
}
