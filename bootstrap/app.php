<?php

use App\Crm\Http\Middleware\RequireAssignedCrmRecord;
use App\Crm\Http\Middleware\RequireCrmPermission;
use App\Crm\Http\Middleware\RestoreCrmQueryString;
use App\Crm\Http\Middleware\SetCrmLocale;
use App\Crm\Http\Middleware\UseCrmBrowserSession;
use App\Crm\Http\Middleware\UseCrmDatabase;
use App\Http\Middleware\EnsureDemoAdminAuthenticated;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([__DIR__.'/../app/Crm/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [RestoreCrmQueryString::class, UseCrmBrowserSession::class]);
        $middleware->alias([
            'admin.demo' => EnsureDemoAdminAuthenticated::class,
            'admin.locale' => SetAdminLocale::class,
            'crm.permission' => RequireCrmPermission::class,
            'crm.assigned' => RequireAssignedCrmRecord::class,
            'crm.locale' => SetCrmLocale::class,
            'crm.database' => UseCrmDatabase::class,
        ]);
        $middleware->prependToPriorityList(SubstituteBindings::class, UseCrmDatabase::class);
        $middleware->validateCsrfTokens(except: ['crm/webhooks/line']);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('crm/*') ? route('crm.login') : route('admin.login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('crm/*') ? route('crm.admin.dashboard') : route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
