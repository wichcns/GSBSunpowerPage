<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GSB EV CRM' }}</title>
    <link rel="stylesheet" href="{{ asset('crm-assets/app-DoAmfeMr.css') }}"><script type="module" src="{{ asset('crm-assets/app-D229W9Q-.js') }}"></script>
</head>

<body class="crm-shell-body">
    <div class="crm-shell">
        <x-crm::admin.sidebar />
        <button class="crm-sidebar-backdrop" type="button" data-nav-backdrop aria-label="{{ __('nav.close_menu') }}"></button>
        <div class="crm-shell__workspace">
            <x-crm-admin.topbar />
            <main class="crm-shell__content">@yield('content')</main>
        </div>
    </div>
</body>

</html>
