<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectPageController extends Controller
{
    public function show(string $type): View
    {
        abort_unless(in_array($type, ['ppa', 'epc', 'ev'], true), 404);

        $projects = DB::table('projects')->where('type', $type)->where('is_published', true)->orderBy('sort_order')->get()->map(function (object $project): array {
            $data = (array) $project;
            $data['image_url'] = $project->image_path ? asset($project->image_path) : null;
            $data['gallery_paths'] = collect(json_decode($project->gallery_paths ?: '[]', true))
                ->map(fn (string $path) => asset($path))->values()->all();

            return $data;
        })->values();

        return view('pages.'.match ($type) {
            'ppa' => 'solar-ppa', 'epc' => 'epc', 'ev' => 'ev-charging',
        }, compact('projects'));
    }
}
