<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\LegacyLeadDetail;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\ReadModels\SiteDirectory;
use App\Crm\Support\AssignedCrmRecords;
use App\Crm\Support\CurrentOpportunity;
use App\Crm\Support\SalesStageGroups;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use JsonException;

class PipelineController extends Controller
{
    public function destroy(string $sourceId, Request $request): RedirectResponse
    {
        DB::transaction(function () use ($sourceId, $request): void {
            abort_if(CurrentOpportunity::record($sourceId, true) === null, 404);
            DB::table('crm_archived_opportunities')->insert([
                'source_id' => $sourceId,
                'archived_by_user_id' => $request->user()->id,
                'archived_at' => now(),
            ]);
        });

        return redirect()->route('crm.admin.pipeline.index');
    }

    public function index(Request $request, LegacyPipeline $pipeline, AssignedCrmRecords $assigned, SiteDirectory $siteDirectory): View
    {
        $query = $request->query();
        $search = mb_substr(trim(is_string($query['q'] ?? null) ? $query['q'] : ''), 0, 100);
        $stage = is_string($query['stage'] ?? null) ? $query['stage'] : '';
        if ($stage !== '' && ! in_array($stage, SalesStageGroups::codes(), true)) {
            $stage = SalesStageGroups::forStage($stage);
        }
        $priority = is_string($query['priority'] ?? null) ? $query['priority'] : '';
        $owner = mb_substr(trim(is_string($query['owner'] ?? null) ? $query['owner'] : ''), 0, 100);
        $page = filter_var($query['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $page = min($page, 100000);
        $viewMode = ($query['view'] ?? null) === 'board' ? 'board' : 'table';

        $all = $pipeline->all();
        if ($request->user()->role?->slug === 'staff') {
            $allowed = array_flip($assigned->pipeline($request->user()));
            $all = array_intersect_key($all, $allowed);
        }
        $rows = array_values(array_filter($all, function (array $row) use ($search, $stage, $priority, $owner) {
            if (
                $search !== '' && ! str_contains(mb_strtolower(implode(' ', array_filter([
                    $row['site_name'],
                    $row['site_code'],
                    $row['area'],
                ]))), mb_strtolower($search))
            ) {
                return false;
            }
            if ($stage !== '' && $row['display_stage_code'] !== $stage) {
                return false;
            }
            if ($priority !== '' && $row['priority_code'] !== $priority) {
                return false;
            }
            if ($owner !== '' && $row['owner'] !== $owner) {
                return false;
            }

            return true;
        }));
        // Show newly created native opportunities first.
        // Preserve the existing order of legacy opportunities.
        usort($rows, static function (array $a, array $b): int {
            $aSource = (string) ($a['source_id'] ?? '');
            $bSource = (string) ($b['source_id'] ?? '');

            $aNative = preg_match('/^native-site-(\d+)$/', $aSource, $aMatch) === 1;
            $bNative = preg_match('/^native-site-(\d+)$/', $bSource, $bMatch) === 1;

            if ($aNative && $bNative) {
                return (int) $bMatch[1] <=> (int) $aMatch[1];
            }

            if ($aNative !== $bNative) {
                return $aNative ? -1 : 1;
            }

            return 0;
        });
        $paginator = new LengthAwarePaginator(array_slice($rows, ($page - 1) * 20, 20), count($rows), 20, $page, [
            'path' => $request->url(),
        ] + ($request->user()->hasPermission('sites.write') || $request->user()->role?->slug === 'staff' ? $siteDirectory->forRequest($request) : []));
        $paginator->appends($request->query());

        $boardGroups = [
            'early' => [],
            'review_mou' => [],
            'contract_signed' => [],
            'paused_closed' => [],
            'other' => [],
        ];
        foreach ($rows as $row) {
            $group = match ($row['display_stage_code']) {
                'NEW', 'NEGOTIATING' => 'early',
                'REVIEW', 'MOU' => 'review_mou',
                'CONTRACT', 'SIGNED' => 'contract_signed',
                'ON_HOLD', 'CANCELLED' => 'paused_closed',
                default => 'other',
            };
            $boardGroups[$group][] = $row;
        }

        return view('crm.admin.pipeline.index', [
            'rows' => $paginator,
            'boardGroups' => $boardGroups,
            'viewMode' => $viewMode,
            'summary' => $pipeline->summaryFor($all),
            'filters' => compact('search', 'stage', 'priority', 'owner'),
            'owners' => collect($all)->pluck('owner')->unique()->sort()->values(),
            'stages' => collect(SalesStageGroups::codes())
                ->when(($pipeline->summaryFor($all)['display_stages']['OTHER'] ?? 0) > 0, fn ($codes) => $codes->push('OTHER'))
                ->mapWithKeys(fn (string $code) => [$code => __('sales_stage_groups.'.$code)])->all(),
            'priorities' => __('pipeline_status.priorities') + collect($all)->pluck('priority_code')->filter()->reject(fn ($code) => isset(LegacyPipeline::PRIORITIES[$code]))->unique()->mapWithKeys(fn ($code) => [$code => $code])->all(),
        ] + $siteDirectory->forRequest($request));
    }

    public function show(string $sourceId, LegacyLeadDetail $detail): JsonResponse
    {
        try {
            $result = $detail->find($sourceId);
        } catch (JsonException) {
            Log::warning('Malformed legacy pipeline detail payload', ['source_id' => $sourceId]);

            return response()->json(['error' => __('crm_errors.detail_load_failed')], 500);
        }

        abort_if($result === null, 404);

        return response()->json($result);
    }
    // public function show(string $sourceId, LegacyLeadDetail $detail): JsonResponse
    // {
    //     try {
    //         $result = $detail->find($sourceId);
    //     } catch (JsonException) {
    //         Log::warning('Malformed legacy pipeline detail payload', ['source_id' => $sourceId]);

    //         return response()->json(['error' => 'ไม่สามารถโหลดรายละเอียดได้'], 500);
    //     }
    //     abort_if($result === null, 404);

    //     return response()->json($result);
    // }
}
