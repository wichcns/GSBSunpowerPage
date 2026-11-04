<?php

namespace App\Crm\Http\Middleware;

use App\Crm\Support\AssignedCrmRecords;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAssignedCrmRecord
{
    public function __construct(private readonly AssignedCrmRecords $records) {}

    public function handle(Request $request, Closure $next, string $area, string $kind = 'any'): Response
    {
        $user = $request->user();
        $managerPermission = $area === 'pipeline' ? 'pipeline.stage' : 'engineering.manage';
        if (($area === 'pipeline' && $user->role?->slug !== 'staff') || ($area !== 'pipeline' && $user->hasPermission($managerPermission))) {
            return $next($request);
        }

        $sourceId = $request->route('sourceId');
        $allowed = $area === 'pipeline' ? $this->records->pipeline($user) : $this->records->engineering($user, $kind);
        abort_unless(in_array($sourceId, $allowed, true), 403);

        return $next($request);
    }
}
