<?php

namespace App\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCrmPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless($request->user()?->hasPermission($permission), 403);

        return $next($request);
    }
}
