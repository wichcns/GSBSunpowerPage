<?php

namespace App\Crm\Actions;

use App\Crm\Services\CrmLineNotifier;
use App\Crm\Support\SalesStages;
use Illuminate\Support\Facades\DB;

class ChangeSalesStage
{
    public function __construct(private readonly EnsureEngineeringProject $projects, private readonly CrmLineNotifier $notifier) {}

    public function execute(string $sourceId, string $stage, string $expectedVersion, int $actorId): array
    {
        abort_if(DB::table('crm_archived_opportunities')->where('source_id', $sourceId)->exists(), 404);
        $probability = SalesStages::probability($stage);
        abort_if($probability === null, 422, __('crm_errors.invalid_sales_stage'));

        return DB::transaction(function () use ($sourceId, $stage, $expectedVersion, $actorId, $probability): array {
            $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
            $lead = $batchId === null ? null : DB::table('crm_legacy_records')
                ->where('batch_id', $batchId)->where('source_table', 'leads')
                ->where('source_id', $sourceId)->lockForUpdate()->first();
            $native = $lead === null ? DB::table('crm_native_opportunities')
                ->where('source_id', $sourceId)->lockForUpdate()->first() : null;
            $lead ??= $native;
            abort_if($lead === null, 404);

            $current = DB::table('crm_pipeline_states')->where('lead_source_id', $sourceId)
                ->lockForUpdate()->first();
            $version = $current === null
                ? ($native === null
                    ? SalesStages::legacyVersion((int) $batchId, $lead->payload_sha256)
                    : SalesStages::nativeVersion((int) $native->id))
                : SalesStages::currentVersion((int) $current->version);
            abort_if($version !== $expectedVersion, 409, __('crm_errors.data_changed'));

            $source = json_decode($lead->payload, true, 512, JSON_THROW_ON_ERROR);
            $oldStage = $current?->stage ?? trim((string) ($source['stage'] ?? ''));
            $oldProbability = $current?->probability ?? ($source['probability'] ?? null);
            if ($oldStage !== $stage) {
                if ($current === null) {
                    DB::table('crm_pipeline_states')->insert([
                        'lead_source_id' => $sourceId, 'stage' => $stage,
                        'probability' => $probability, 'version' => 1,
                        'updated_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                } else {
                    DB::table('crm_pipeline_states')->where('id', $current->id)->update([
                        'stage' => $stage, 'probability' => $probability,
                        'version' => $current->version + 1, 'updated_by_user_id' => $actorId,
                        'updated_at' => now(),
                    ]);
                }
                $historyId = DB::table('crm_stage_histories')->insertGetId([
                    'lead_source_id' => $sourceId, 'from_stage' => $oldStage ?: null,
                    'to_stage' => $stage, 'from_probability' => is_numeric($oldProbability) && $oldProbability >= 0 && $oldProbability <= 100 ? (int) $oldProbability : null,
                    'to_probability' => $probability, 'actor_user_id' => $actorId,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->notifier->salesStage($sourceId, (string) ($source['site_name'] ?? 'ไม่ระบุชื่อ'), $oldStage, $stage, $actorId, $historyId);
            }

            $project = SalesStages::createsEngineeringProject($stage)
                ? $this->projects->forLead($sourceId, $native === null ? (int) $batchId : null)
                : null;

            return ['project' => $project];
        });
    }
}
