<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveCrmBusinessTermsRequest;
use App\Crm\Models\CrmBusinessTerms;
use App\Crm\Models\CrmBusinessTermsRevision;
use App\Crm\ReadModels\LegacyContracts;
use App\Crm\ReadModels\LegacyLeadDetail;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\Support\CurrentOpportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractsController extends Controller
{
    public function updateTerms(string $sourceId, Request $request, LegacyContracts $contracts): RedirectResponse
    {
        abort_unless(isset($contracts->all()[$sourceId]), 404);
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:0'],
            'parking_spaces' => ['nullable', 'integer', 'min:0'],
            'commercial_model' => ['nullable', Rule::in(SaveCrmBusinessTermsRequest::COMMERCIAL_MODELS)],
            'contract_term' => ['nullable', 'string', 'max:255'],
            'contract_signed_at' => ['nullable', 'date_format:Y-m-d'],
            'contract_effective_at' => ['nullable', 'date_format:Y-m-d'],
            'rent_free_months' => ['nullable', 'integer', 'min:0'],
            'monthly_payment_day' => ['nullable', 'integer', 'between:1,31'],
        ]);

        DB::transaction(function () use ($sourceId, $data, $request): void {
            $terms = CrmBusinessTerms::query()->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            abort_if(($terms?->version ?? 0) !== (int) $data['version'], 409);
            $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
            $legacyRecord = CurrentOpportunity::record($sourceId, true);
            abort_if($legacyRecord === null, 404);
            $before = $terms?->values ?? (json_decode($legacyRecord->payload, true, 512, JSON_THROW_ON_ERROR) ?: []);
            $fields = ['parking_spaces', 'commercial_model', 'contract_term', 'contract_signed_at', 'contract_effective_at', 'rent_free_months', 'monthly_payment_day'];
            $values = $before;
            $changes = [];
            foreach ($fields as $field) {
                $new = $data[$field] ?? null;
                if (($before[$field] ?? null) != $new) {
                    $changes[$field] = ['before' => $before[$field] ?? null, 'after' => $new];
                }
                $values[$field] = $new;
            }
            if ($changes === []) {
                return;
            }
            $version = ($terms?->version ?? 0) + 1;
            CrmBusinessTerms::query()->updateOrCreate(['lead_source_id' => $sourceId], [
                'values' => $values, 'version' => $version, 'updated_by_user_id' => $request->user()->id,
            ]);
            CrmBusinessTermsRevision::query()->create([
                'lead_source_id' => $sourceId, 'version' => $version, 'changed_by_user_id' => $request->user()->id,
                'changes' => $changes, 'created_at' => now(),
            ]);
        });

        return redirect()->route('crm.admin.contracts.index', ['open' => $sourceId])->with('status', __('contract_terms.save'));
    }

    public function show(string $sourceId, LegacyContracts $contracts, LegacyLeadDetail $details): JsonResponse
    {
        abort_unless(isset($contracts->all()[$sourceId]), 404);
        $detail = $details->find($sourceId);
        abort_if($detail === null, 404);

        return response()->json([
            'opportunity' => $detail['opportunity'],
            'business' => $detail['sections']['business'],
            'attachments' => array_values(array_filter($detail['sections']['attachments'],
                static fn (array $file): bool => strtoupper((string) ($file['category'] ?? '')) === 'CONTRACT')),
        ]);
    }

    public function index(Request $request, LegacyContracts $contracts): View
    {
        $query = $request->query();
        $search = mb_substr(trim(is_string($query['q'] ?? null) ? $query['q'] : ''), 0, 100);
        $stage = is_string($query['stage'] ?? null) ? $query['stage'] : '';
        $status = mb_substr(trim(is_string($query['contract_status'] ?? null) ? $query['contract_status'] : ''), 0, 100);
        $expiry = is_string($query['expiry'] ?? null) && in_array($query['expiry'], ['expired', 'next_90', 'missing', 'invalid'], true)
            ? $query['expiry'] : '';
        $information = ($query['information'] ?? null) === 'missing' ? 'missing' : '';
        $document = is_string($query['document'] ?? null) && in_array($query['document'], ['current', 'legacy_metadata', 'not_found'], true)
            ? $query['document'] : '';
        $page = filter_var($query['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $page = min($page, 100000);

        $all = array_values($contracts->all());
        $stages = collect(array_keys(LegacyPipeline::STAGES))->mapWithKeys(fn (string $code) => [$code => __("stages.sales.{$code}")])->all();
        if (! isset($stages[$stage])) {
            $stage = '';
        }
        $statuses = collect($all)->pluck('contract_status')->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
            ->mapWithKeys(fn ($value) => [(string) $value => (string) $value])->sortKeys()->all();
        if (! isset($statuses[$status])) {
            $status = '';
        }
        $rows = array_values(array_filter($all, static function (array $row) use ($search, $stage, $status, $expiry, $information, $document): bool {
            if ($search !== '' && ! str_contains(mb_strtolower(implode(' ', [
                $row['site_name'] ?? '', $row['site_code'] ?? '', $row['area'] ?? '',
            ])), mb_strtolower($search))) {
                return false;
            }

            return ($stage === '' || $row['stage_code'] === $stage)
                && ($status === '' || $row['contract_status'] === $status)
                && ($expiry === '' || $row['expiry_class'] === $expiry)
                && ($information === '' || $row['missing_fields'] !== [])
                && ($document === '' || $row['document_status'] === $document);
        }));
        $paginator = new LengthAwarePaginator(array_slice($rows, ($page - 1) * 20, 20), count($rows), 20, $page, [
            'path' => $request->url(),
        ]);
        $paginator->appends($request->query());

        return view('crm.admin.contracts.index', [
            'rows' => $paginator,
            'summary' => [
                'total' => count($all),
                'expired' => count(array_filter($all, fn ($row) => $row['expiry_class'] === 'expired')),
                'next_90' => count(array_filter($all, fn ($row) => $row['expiry_class'] === 'next_90')),
                'missing' => count(array_filter($all, fn ($row) => $row['expiry_class'] === 'missing')),
                'missing_information' => count(array_filter($all, fn ($row) => $row['missing_fields'] !== [])),
                'document_not_found' => count(array_filter($all, fn ($row) => $row['document_status'] === 'not_found')),
            ],
            'stages' => $stages,
            'statuses' => $statuses,
            'filters' => compact('search', 'stage', 'status', 'expiry', 'information', 'document'),
        ]);
    }
}
