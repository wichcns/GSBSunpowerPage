<?php

namespace App\Crm\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['locale' => ['required', Rule::in(['th', 'en', 'zh'])]]);
        $request->user()->update(['locale' => $data['locale']]);
        $request->session()->put('locale', $data['locale']);

        return back();
    }
}
