<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __invoke(): View
    {
        $aboutChartPath = DB::table('site_settings')->where('key', 'about_org_chart')->value('value') ?: 'image/organization-chart.png';

        return view('pages.about', compact('aboutChartPath'));
    }
}
