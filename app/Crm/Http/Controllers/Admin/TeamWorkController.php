<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\TeamWork;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamWorkController extends Controller
{
    public function index(Request $request, TeamWork $work): View
    {
        $filters = $request->validate(['staff' => ['nullable', 'integer', 'min:1']]);

        return view('crm.admin.team-work', [
            'staff' => $work->forStaff(),
            'selectedStaff' => isset($filters['staff']) ? (int) $filters['staff'] : null,
        ]);
    }
}
