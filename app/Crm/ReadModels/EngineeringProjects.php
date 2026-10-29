<?php

namespace App\Crm\ReadModels;

use Illuminate\Support\Facades\DB;

class EngineeringProjects
{
    public function __construct(private readonly LegacyPipeline $pipeline) {}

    public function all(): array
    {
        $leads = $this->pipeline->all();
        $rows = [];
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        if ($batchId !== null) {
            foreach (DB::table('crm_legacy_records')->where('batch_id', $batchId)
                ->where('source_table', 'projects')->get(['payload', 'payload_sha256']) as $record) {
                $project = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
                $sourceId = (string) ($project['lead_id'] ?? '');
                if ($sourceId === '' || ! isset($leads[$sourceId]) || isset($rows[$sourceId])) {
                    continue;
                }
                $rows[$sourceId] = [
                    'lead_source_id' => $sourceId,
                    'site_name' => $leads[$sourceId]['site_name'],
                    'project_code' => $project['project_code'] ?? 'ยังไม่ระบุ',
                    'engineering_stage' => $project['engineering_stage'] ?? 'ยังไม่ระบุ',
                    'sync_status' => $project['sync_status'] ?? null,
                    'origin' => 'legacy',
                    'version' => "legacy:{$batchId}:{$record->payload_sha256}",
                ];
            }
        }

        foreach (DB::table('crm_engineering_projects')->get() as $project) {
            if (! isset($leads[$project->lead_source_id])) {
                continue;
            }
            $rows[$project->lead_source_id] = [
                'lead_source_id' => $project->lead_source_id,
                'site_name' => $leads[$project->lead_source_id]['site_name'],
                'project_code' => $project->project_code,
                'engineering_stage' => $project->engineering_stage,
                'sync_status' => $project->sync_status,
                'origin' => $project->origin,
                'version' => "current:{$batchId}:{$project->version}",
            ];
        }

        return array_values($rows);
    }

    public function find(string $leadSourceId): ?array
    {
        foreach ($this->all() as $project) {
            if ($project['lead_source_id'] === $leadSourceId) {
                return $project;
            }
        }

        return null;
    }

    public function historyFor(string $leadSourceId): array
    {
        return DB::table('crm_engineering_stage_histories as history')
            ->leftJoin('crm_users as actor', 'actor.id', '=', 'history.actor_user_id')
            ->where('history.lead_source_id', $leadSourceId)
            ->orderByDesc('history.id')
            ->get(['history.id', 'history.from_stage', 'history.to_stage',
                'history.created_at', 'actor.name as actor_name'])
            ->map(fn ($row) => (array) $row)->all();
    }
}
