@php
    $items = [
        ['label' => match (auth()->user()?->role?->slug) {
            'manager' => __('dashboard.management_title'),
            'staff' => __('dashboard.tasks_title'),
            default => __('nav.dashboard'),
        }, 'route' => 'crm.admin.dashboard', 'icon' => '⌁', 'permission' => 'dashboard.view'],
        ['label' => __('nav.reports'), 'route' => 'crm.admin.reports.index', 'icon' => '▤', 'permission' => 'reports.view'],
        ['label' => __('nav.pipeline'), 'route' => 'crm.admin.pipeline.index', 'icon' => '↗', 'permission' => 'pipeline.view'],
        ['label' => __('nav.contracts'), 'route' => 'crm.admin.contracts.index', 'icon' => '▣', 'permission' => 'contracts.view'],
        ['label' => __('nav.team_work'), 'route' => 'crm.admin.team-work', 'icon' => '◎', 'permission' => 'team.view'],
        ['label' => __('nav.engineering'), 'route' => 'crm.admin.engineering.index', 'icon' => '⚙', 'permission' => 'engineering.view'],
        ['label' => __('nav.my_work'), 'route' => 'crm.admin.my-work', 'icon' => '✓', 'permission' => 'work.view'],
        ['label' => __('nav.activities'), 'route' => 'crm.admin.activities.index', 'icon' => '◷', 'permission' => 'activities.view'],
        ['label' => __('manuals.title'), 'route' => 'crm.admin.manuals.index', 'icon' => '▥', 'permission' => 'dashboard.view'],
        ['label' => __('nav.integrations'), 'route' => 'crm.admin.integrations.index', 'icon' => '⌘', 'permission' => 'system.manage'],
        ['label' => __('nav.settings'), 'route' => 'crm.admin.settings.index', 'icon' => '☼', 'permission' => 'system.manage'],
    ];
@endphp

<aside id="admin-sidebar" class="crm-sidebar" aria-label="{{ __('nav.main_menu') }}">
    <a href="{{ route('crm.admin.dashboard') }}" class="crm-brand">
        <span>
            <img class="crm-brand__logo" src="{{ asset('crm-assets/gsb-ev-logo.png') }}" alt="GSB EV">
            <small>{{ __('nav.platform') }}</small>
        </span>
    </a>

    <nav class="crm-sidebar__nav" aria-label="{{ __('nav.system_menu') }}">
        @foreach ($items as $item)
            @continue(!auth()->user()?->hasPermission($item['permission']))
            <a class="crm-nav-item {{ request()->routeIs($item['route']) ? 'is-active' : '' }}" href="{{ route($item['route']) }}">
                <span aria-hidden="true">{{ $item['icon'] }}</span><span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="crm-sidebar__footer"><span class="crm-status-dot" aria-hidden="true"></span>{{ __('nav.secure_workspace') }}</div>
</aside>
