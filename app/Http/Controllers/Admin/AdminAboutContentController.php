<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAboutContentController extends Controller
{
    public function editChart(): View
    {
        $chartPath = DB::table('site_settings')->where('key', 'about_org_chart')->value('value') ?: 'image/organization-chart.png';

        return view('admin.website-content.about-chart', compact('chartPath'));
    }

    public function updateChart(Request $request): RedirectResponse
    {
        $request->validate(['chart' => ['required', 'image', 'max:5120']]);
        $directory = (is_file(base_path('index.php')) ? base_path() : public_path()).'/uploads/about';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file = $request->file('chart');
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $name);
        DB::table('site_settings')->updateOrInsert(['key' => 'about_org_chart'], ['value' => 'uploads/about/'.$name, 'updated_at' => now(), 'created_at' => now()]);

        return back()->with('success', 'อัปเดตรูป Organization Chart เรียบร้อยแล้ว');
    }
}
