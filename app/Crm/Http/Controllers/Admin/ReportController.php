<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Exports\ManagementReportWorkbook;
use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\ManagementReport;
use App\Crm\Support\EngineeringStages;
use App\Crm\Support\SalesStageGroups;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    public function index(Request $request, ManagementReport $reports)
    {
        $filters = $this->filters($request);
        $report = $reports->forUser($request->user(), $filters);
        $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $page = min($page, 100000);
        $records = new LengthAwarePaginator(array_slice($report['rows'], ($page - 1) * 25, 25), count($report['rows']), 25, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        return view('crm.admin.reports.index', [
            'title' => __('reports.title'), 'report' => $report, 'records' => $records,
            'filters' => $filters,
            'salesStages' => collect(SalesStageGroups::codes())->mapWithKeys(fn ($code) => [$code => __('sales_stage_groups.'.$code)])->all(),
            'engineeringStages' => collect(array_keys(EngineeringStages::LABELS))->mapWithKeys(fn ($code) => [$code => __('stages.engineering.'.$code)])->all(),
        ]);
    }

    public function export(Request $request, ManagementReport $reports, ManagementReportWorkbook $workbook)
    {
        $filters = $this->filters($request);

        return $workbook->download($reports->forUser($request->user(), $filters), $filters);
    }

    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'owner' => ['nullable', 'integer'], 'area' => ['nullable', 'string', 'max:100'],
            'location_type' => ['nullable', 'string', 'max:100'], 'sales_stage' => ['nullable', 'string', 'max:50'],
            'engineering_stage' => ['nullable', 'string', 'max:50'], 'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        if (filled($filters['sales_stage'] ?? null)) {
            $filters['sales_stage'] = SalesStageGroups::forStage($filters['sales_stage']);
        }

        return $filters;
    }
}
