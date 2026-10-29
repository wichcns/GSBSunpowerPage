<?php

namespace App\Crm\Providers;

use App\Crm\View\Components\AdminTopbar;
use App\Crm\View\Components\AppLayout;
use App\Crm\View\Components\GuestLayout;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/crm/components'), 'crm');
        Blade::component(GuestLayout::class, 'guest-layout', 'crm');
        Blade::component(AppLayout::class, 'app-layout', 'crm');
        Blade::component(AdminTopbar::class, 'admin.topbar', 'crm');
    }
}
