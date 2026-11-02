<?php

namespace App\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseCrmBrowserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('crm', 'crm/*')) {
            return $next($request);
        }

        $original = [
            'session.cookie' => config('session.cookie'),
            'session.path' => config('session.path'),
            'session.expire_on_close' => config('session.expire_on_close'),
        ];

        config([
            'session.cookie' => 'crm_browser_session',
            'session.path' => '/crm',
            'session.expire_on_close' => true,
        ]);

        app('session')->driver()->setName('crm_browser_session');
        try {
            return $next($request);
        } finally {
            config($original);
            app('session')->driver()->setName($original['session.cookie']);
        }
    }
}
