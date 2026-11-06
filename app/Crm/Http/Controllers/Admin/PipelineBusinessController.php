<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveCrmBusinessTermsRequest;
use App\Crm\Models\CrmBusinessTerms;
use App\Crm\Models\CrmBusinessTermsRevision;
use App\Crm\Support\CurrentOpportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PipelineBusinessController extends Controller
{
    public function update(string $sourceId, SaveCrmBusinessTermsRequest $request): JsonResponse
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $record = CurrentOpportunity::record($sourceId);
        abort_if($record === null, 404);

        $values = $request->validated('values');
        $values = array_replace(array_fill_keys(SaveCrmBusinessTermsRequest::FIELDS, null), $values);
        $version = $request->validated('version');
        $legacy = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
        $result = DB::transaction(function () use ($sourceId, $values, $version, $legacy, $request): array {
            $now = now();
            $legacyValues = array_replace(array_fill_keys(SaveCrmBusinessTermsRequest::FIELDS, null),
                array_intersect_key($legacy, array_flip(SaveCrmBusinessTermsRequest::FIELDS)));
            $existing = CrmBusinessTerms::query()->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            if ($existing === null && $version === null && $legacyValues === $values) {
                return ['version' => null, 'business' => $legacyValues,
                    'updated_by' => null, 'updated_at' => null];
            }
            DB::table('crm_business_terms')->insertOrIgnore([
                'lead_source_id' => $sourceId, 'values' => '{}', 'version' => 0,
                'updated_by_user_id' => $request->user()->id, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $terms = CrmBusinessTerms::query()->where('lead_source_id', $sourceId)->lockForUpdate()->firstOrFail();
            if ($terms->version !== ($version ?? 0)) {
                abort(409, __('crm_errors.data_changed'));
            }
            $before = $terms->version === 0 ? $legacyValues : $terms->values;
            foreach (SaveCrmBusinessTermsRequest::NEW_CONTRACT_FIELDS as $field) {
                if (! array_key_exists($field, $request->validated('values'))) {
                    $values[$field] = $before[$field] ?? null;
                }
            }
            $changes = [];
            foreach (SaveCrmBusinessTermsRequest::FIELDS as $field) {
                $old = $before[$field] ?? null;
                $new = $values[$field];
                if ($old !== $new) {
                    $changes[$field] = ['before' => $old, 'after' => $new];
                }
            }
            if ($changes === [] && $terms->version > 0) {
                return ['version' => $terms->version, 'business' => $terms->values,
                    'updated_by' => $terms->editor?->name, 'updated_at' => $terms->updated_at?->toIso8601String()];
            }
            $terms->fill(['values' => $values, 'version' => $terms->version + 1,
                'updated_by_user_id' => $request->user()->id])->save();
            CrmBusinessTermsRevision::query()->create([
                'lead_source_id' => $sourceId, 'version' => $terms->version,
                'changed_by_user_id' => $request->user()->id, 'changes' => $changes, 'created_at' => $now,
            ]);

            return ['version' => $terms->version, 'business' => $terms->values,
                'updated_by' => $request->user()->name, 'updated_at' => $terms->updated_at?->toIso8601String()];
        });

        return response()->json($result);
    }
}
