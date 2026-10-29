<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Exports\ManagementReportWorkbook;
use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\ReadModels\ManagementReport;
use App\Crm\Support\EngineeringStages;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, ManagementReport $reports)
    {
        $filters = $this->filters($request);

        return view('crm.admin.reports.index', [
            'title' => __('reports.title'), 'report' => $reports->forUser($request->user(), $filters),
            'filters' => $filters,
            'salesStages' => collect(array_keys(LegacyPipeline::STAGES))->mapWithKeys(fn ($code) => [$code => __('stages.sales.'.$code)])->all(),
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
        return $request->validate([
            'owner' => ['nullable', 'integer'], 'area' => ['nullable', 'string', 'max:100'],
            'location_type' => ['nullable', 'string', 'max:100'], 'sales_stage' => ['nullable', 'string', 'max:50'],
            'engineering_stage' => ['nullable', 'string', 'max:50'], 'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
    }
}
