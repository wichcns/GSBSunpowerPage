<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'summary' => [
                ['label' => __('admin.ppa'), 'value' => DB::table('projects')->where('type', 'ppa')->count(), 'icon' => 'fas fa-solar-panel', 'colour' => 'info', 'route' => 'admin.projects', 'type' => 'ppa'],
                ['label' => __('admin.epc'), 'value' => DB::table('projects')->where('type', 'epc')->count(), 'icon' => 'fas fa-hard-hat', 'colour' => 'success', 'route' => 'admin.projects', 'type' => 'epc'],
                ['label' => __('admin.ev'), 'value' => DB::table('projects')->where('type', 'ev')->count(), 'icon' => 'fas fa-charging-station', 'colour' => 'warning', 'route' => 'admin.projects', 'type' => 'ev'],
                ['label' => __('admin.enquiries'), 'value' => DB::table('contact_enquiries')->where('status', 'new')->count(), 'icon' => 'fas fa-inbox', 'colour' => 'secondary', 'route' => 'admin.contact-enquiries'],
            ],
        ]);
    }
}
