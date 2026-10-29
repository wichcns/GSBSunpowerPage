<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\ActivityCenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request, ActivityCenter $activities): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(array_keys(ActivityCenter::TYPES))],
            'owner' => ['nullable', 'integer', 'min:1'],
            'due' => ['nullable', Rule::in(['today', 'overdue', 'undated', 'upcoming'])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        return view('crm.admin.activities.index', $activities->forUser($request->user(), $filters) + [
            'filters' => $filters,
        ]);
    }
}
