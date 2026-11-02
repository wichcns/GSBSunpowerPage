<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Services\GlobalCrmSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalSearchController extends Controller
{
    public function index(Request $request, GlobalCrmSearch $search): View|RedirectResponse
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = mb_substr(trim((string) ($validated['q'] ?? '')), 0, 100);
        if ($query === '') {
            return redirect()->route('crm.admin.dashboard');
        }

        return view('crm.admin.search.index', ['query' => $query, 'results' => $search->for($request->user(), $query)]);
    }
}
