<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\DashboardOverview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardOverview $dashboard): View
    {
        return view('crm.admin.dashboard', ['overview' => $dashboard->forUser($request->user())]);
    }
}
