<?php

namespace App\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCrmLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user('crm')?->locale ?? $request->session()->get('locale', 'th');
        app()->setLocale(in_array($locale, ['th', 'en', 'zh'], true) ? $locale : 'th');

        return $next($request);
    }
}
