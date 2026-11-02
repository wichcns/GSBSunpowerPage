<?php

namespace App\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestoreCrmQueryString
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || ! $request->is('crm/*') || $request->query->count() !== 0) {
            return $next($request);
        }

        $rawQuery = (string) $request->server('QUERY_STRING', '');
        if ($rawQuery === '') {
            $rawQuery = (string) parse_url((string) $request->server('REQUEST_URI', ''), PHP_URL_QUERY);
        }

        if ($rawQuery !== '' && strlen($rawQuery) <= 4096) {
            parse_str($rawQuery, $parameters);
            $request->query->replace($parameters);
        }

        return $next($request);
    }
}
