<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use Illuminate\View\View;

class SolutionPageController extends Controller
{
    public function __invoke(): View
    {
        $solutions = Solution::query()->where('is_published', true)->orderBy('sort_order')->get();

        return view('pages.solutions', compact('solutions'));
    }
}
