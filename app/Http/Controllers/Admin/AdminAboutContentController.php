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
        $chartPath = $this->chartPath();

        return view('admin.website-content.about-chart', compact('chartPath'));
    }

    public function updateChart(Request $request): RedirectResponse
    {
        $request->validate(['chart' => ['required', 'image', 'max:5120']]);
        $webRoot = is_file(base_path('index.php')) ? base_path() : public_path();
        $uploadsDirectory = is_dir($webRoot.DIRECTORY_SEPARATOR.'Uploads') ? 'Uploads' : 'uploads';
        $directory = $webRoot.DIRECTORY_SEPARATOR.$uploadsDirectory.DIRECTORY_SEPARATOR.'about';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('chart');
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $name);
        $path = $uploadsDirectory.'/about/'.$name;
        DB::table('site_settings')->updateOrInsert(['key' => 'about_org_chart'], ['value' => json_encode($path, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), 'updated_at' => now(), 'created_at' => now()]);

        return back()->with('success', 'อัปเดตรูป Organization Chart เรียบร้อยแล้ว');
    }

    private function chartPath(): string
    {
        $value = DB::table('site_settings')->where('key', 'about_org_chart')->value('value');
        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_string($decoded) ? $decoded : (is_string($value) && $value !== '' ? $value : 'image/organization-chart.png');
    }
}
