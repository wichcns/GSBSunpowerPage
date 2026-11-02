<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __invoke(): View
    {
        $value = DB::table('site_settings')->where('key', 'about_org_chart')->value('value');
        $decoded = is_string($value) ? json_decode($value, true) : null;
        $aboutChartPath = is_string($decoded) ? $decoded : (is_string($value) && $value !== '' ? $value : 'image/organization-chart.png');

        return view('pages.about', compact('aboutChartPath'));
    }
}
