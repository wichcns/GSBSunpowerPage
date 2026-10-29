<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->is_admin) {
            return $next($request);
        }

        return redirect()->route('admin.login');
    }
}
