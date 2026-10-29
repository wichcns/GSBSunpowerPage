<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveUnifiedLeadRequest;
use App\Crm\Http\Requests\StoreCrmVisitRequest;
use App\Crm\Models\CrmVisitLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PipelineVisitController extends Controller
{
    public function store(string $sourceId, StoreCrmVisitRequest $request): JsonResponse
    {
        $this->assertCurrentLead($sourceId);
        $visit = DB::transaction(function () use ($sourceId, $request): CrmVisitLog {
            $this->lockLead($sourceId);
            $visit = CrmVisitLog::query()->create($request->validated() + [
                'lead_source_id' => $sourceId,
                'created_by_user_id' => $request->user()->id,
            ]);
            $this->syncNextAction($sourceId, $request);

            return $visit;
        });

        return response()->json(['visit' => $visit], 201);
    }

    public function update(string $sourceId, CrmVisitLog $visit, StoreCrmVisitRequest $request): JsonResponse
    {
        $this->assertCurrentLead($sourceId);
        abort_unless($visit->lead_source_id === $sourceId, 404);
        DB::transaction(function () use ($visit, $request, $sourceId): void {
            $this->lockLead($sourceId);
            $visit->update($request->validated());
            $this->syncNextAction($sourceId, $request);
        });

        return response()->json(['visit' => $visit->fresh()]);
    }

    private function assertCurrentLead(string $sourceId): void
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        abort_if($batchId === null || ! DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->where('source_id', $sourceId)->exists(), 404);
    }

    private function lockLead(string $sourceId): void
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->where('source_id', $sourceId)->lockForUpdate()->first();
    }

    private function syncNextAction(string $sourceId, StoreCrmVisitRequest $request): void
    {
        $current = DB::table('crm_lead_details')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
        if ($current === null) {
            $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
            $payload = DB::table('crm_legacy_records')->where('batch_id', $batchId)
                ->where('source_table', 'leads')->where('source_id', $sourceId)->value('payload');
            $legacy = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            $profile = array_replace(array_fill_keys(SaveUnifiedLeadRequest::PROFILE, null),
                array_intersect_key($legacy, array_flip(SaveUnifiedLeadRequest::PROFILE)));
            $technical = array_replace(array_fill_keys(SaveUnifiedLeadRequest::TECHNICAL, null),
                array_intersect_key($legacy, array_flip(SaveUnifiedLeadRequest::TECHNICAL)));
        } else {
            $profile = json_decode($current->profile, true, 512, JSON_THROW_ON_ERROR);
            $technical = json_decode($current->technical, true, 512, JSON_THROW_ON_ERROR);
        }
        $nextAction = $request->validated('next_steps');
        $nextActionAt = $request->validated('next_action_at');
        if (($profile['next_action'] ?? null) === $nextAction && ($profile['next_action_at'] ?? null) === $nextActionAt) {
            return;
        }
        $changes = ['profile' => [
            'next_action' => ['before' => $profile['next_action'] ?? null, 'after' => $nextAction],
            'next_action_at' => ['before' => $profile['next_action_at'] ?? null, 'after' => $nextActionAt],
        ]];
        $profile['next_action'] = $nextAction;
        $profile['next_action_at'] = $nextActionAt;
        $now = now();
        $version = ($current?->version ?? 0) + 1;
        DB::table('crm_lead_details')->updateOrInsert(['lead_source_id' => $sourceId], [
            'profile' => json_encode($profile, JSON_THROW_ON_ERROR),
            'technical' => json_encode($technical, JSON_THROW_ON_ERROR),
            'version' => $version, 'updated_by_user_id' => $request->user()->id,
            'created_at' => $current?->created_at ?? $now, 'updated_at' => $now,
        ]);
        DB::table('crm_lead_detail_revisions')->insert([
            'lead_source_id' => $sourceId, 'version' => $version, 'changed_by_user_id' => $request->user()->id,
            'changes' => json_encode($changes, JSON_THROW_ON_ERROR), 'created_at' => $now,
        ]);
    }
}
