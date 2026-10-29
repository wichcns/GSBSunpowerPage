<?php

namespace App\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UseCrmDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing') || ! config('database.crm_enabled')) {
            return $next($request);
        }

        $original = DB::getDefaultConnection();
        $originalCache = Cache::getDefaultDriver();
        DB::setDefaultConnection('crm');
        Cache::setDefaultDriver('file');

        try {
            return $next($request);
        } finally {
            Cache::setDefaultDriver($originalCache);
            DB::setDefaultConnection($original);
        }
    }
}
