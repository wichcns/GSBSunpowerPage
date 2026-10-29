<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard')) | GSB SunPower Group</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <style>
        .brand-link {
            background: #063a42;
        }

        .main-sidebar {
            background: #082f35;
        }

        .nav-sidebar .nav-link.active {
            background: #0b8279 !important;
        }

        .content-header h1 {
            font-weight: 700;
        }

        .small-box .icon>i {
            font-size: 54px;
            top: 15px;
        }

        .admin-add-project {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .48rem .9rem .48rem .48rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #0b9a8f, #05756e);
            box-shadow: 0 7px 16px rgba(7, 125, 118, .24);
            color: #fff !important;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .01em;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .admin-add-project:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(7, 125, 118, .32);
        }

        .admin-add-project span {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            font-size: .78rem;
        }

        .admin-language-switcher {
            display: flex;
            gap: 4px;
            margin-right: .7rem;
            padding: .2rem;
            border-radius: 999px;
            background: #edf4f3;
        }

        .admin-language-switcher form {
            margin: 0;
        }

        .admin-language-switcher button {
            border: 0;
            padding: .3rem .5rem;
            border-radius: 999px;
            background: transparent;
            color: #527077;
            font-size: .72rem;
            font-weight: 800;
            cursor: pointer;
        }

        .admin-language-switcher button.is-active {
            background: #087d76;
            color: #fff;
            box-shadow: 0 2px 7px rgba(8, 125, 118, .24);
        }

        /* A lighter, calmer Admin workspace while retaining AdminLTE behaviour. */
        body { background: #f3f7f7; color: #153e45; }
        .content-wrapper { background: radial-gradient(circle at 88% 2%, rgba(62, 203, 182, .12), transparent 25%), #f3f7f7; }
        .main-header { min-height: 64px; border-bottom: 1px solid #e1ecea; box-shadow: 0 2px 18px rgba(11, 68, 73, .04); }
        .main-sidebar { background: linear-gradient(180deg, #063a42 0%, #072c33 100%); }
        .brand-link { height: 64px; padding: 18px 18px; background: transparent; font-size: .95rem; letter-spacing: .02em; }
        .brand-link strong { color: #67e1cb; }
        .sidebar .nav-header { margin-top: 18px; color: #78aaa8; font-size: .62rem; font-weight: 800; letter-spacing: .13em; }
        .nav-sidebar .nav-link { margin: 3px 10px; border-radius: 10px; color: #cde4e1; }
        .nav-sidebar .nav-link:hover { background: rgba(125, 234, 211, .12); color: #fff; }
        .nav-sidebar .nav-link.active { box-shadow: 0 7px 18px rgba(0, 0, 0, .14); }
        .content-header { padding: 26px 8px 16px; }
        .admin-workspace-back { display: inline-flex; align-items: center; gap: .45rem; margin-top: .45rem; padding: .48rem .7rem; border: 1px solid #cce5e0; border-radius: 9px; background: rgba(255,255,255,.78); color: #087d76; font-size: .74rem; font-weight: 800; text-decoration: none; transition: .18s ease; }
        .admin-workspace-back:hover { border-color: #087d76; background: #087d76; color: #fff; text-decoration: none; transform: translateX(-2px); }
        .content-header h1 { color: #073f47; font-size: 1.75rem; letter-spacing: -.035em; }
        .card { border: 1px solid #dce9e7; border-radius: 16px; box-shadow: 0 10px 28px rgba(17, 70, 77, .07); overflow: hidden; }
        .card.card-outline { border-top: 0; }
        .card-header { min-height: 68px; padding: 18px 20px; border-bottom: 1px solid #e4efed; background: rgba(255,255,255,.84); }
        .card-title { color: #0a4f58; font-weight: 800; }
        .card-body { padding: 22px; }
        .card-footer { padding: 16px 20px; border-top: 1px solid #e4efed; background: #fbfdfd; }
        .form-control { min-height: 42px; border-color: #cedfdd; border-radius: 9px; box-shadow: none; }
        .form-control:focus { border-color: #16a990; box-shadow: 0 0 0 .18rem rgba(22, 169, 144, .12); }
        .form-group > label { color: #28545a; font-size: .78rem; font-weight: 800; }
        .btn { border-radius: 9px; font-weight: 750; }
        .btn-teal { background: linear-gradient(135deg, #0ca891, #08756e); border-color: transparent; box-shadow: 0 7px 15px rgba(8, 117, 110, .2); }
        .table thead th { padding: 15px 20px; border-top: 0; border-bottom: 1px solid #dbe8e6; color: #557277; font-size: .7rem; font-weight: 850; letter-spacing: .05em; text-transform: uppercase; }
        .table td { padding: 16px 20px; vertical-align: middle; border-color: #e7efee; }
        .table-hover tbody tr:hover { background: #f4fbfa; }
        .admin-project-record { display: flex; gap: 11px; align-items: center; min-width: 260px; }
        .admin-project-record > img, .admin-project-record > span { display: grid; flex: 0 0 42px; width: 42px; height: 42px; place-items: center; border-radius: 10px; background: #e4f5f1; color: #0e9984; object-fit: cover; }
        .admin-project-record strong { display: block; color: #163f46; font-size: .84rem; }
        .admin-project-record small { display: block; margin-top: 2px; color: #789094; font-size: .72rem; }
        .admin-project-form-card .card-header { background: linear-gradient(100deg, #f2fffc, #fff); }
        .admin-media-manager { margin: 1.25rem 0; padding: 1.2rem; border: 1px solid #d8e8e5; border-radius: 14px; background: #f7fbfa; }
        .admin-media-manager__heading { display: flex; gap: .75rem; align-items: center; margin-bottom: 1rem; }
        .admin-media-manager__heading > span { display: grid; width: 38px; height: 38px; place-items: center; border-radius: 11px; color: #fff; background: linear-gradient(135deg, #10af96, #05736d); }
        .admin-media-manager h4 { margin: 0; color: #084b54; font-size: .95rem; font-weight: 800; }
        .admin-media-manager p { margin: .12rem 0 0; color: #6b8588; font-size: .76rem; }
        .admin-media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(154px, 1fr)); gap: 12px; }
        .admin-media-card { position: relative; display: block; min-height: 132px; margin: 0; overflow: hidden; border: 1px solid #d4e4e1; border-radius: 11px; background: #fff; cursor: grab; transition: transform .18s ease, box-shadow .18s ease; }
        .admin-media-card:hover { transform: translateY(-2px); box-shadow: 0 9px 20px rgba(4, 76, 80, .11); }
        .admin-media-card.is-dragging { opacity: .45; cursor: grabbing; }
        .admin-media-card img { display: block; width: 100%; height: 132px; object-fit: cover; }
        .admin-media-card__tag { position: absolute; top: 8px; left: 34px; padding: 3px 7px; border-radius: 99px; color: #075049; background: rgba(238,255,250,.94); font-size: .64rem; font-weight: 800; }
        .admin-media-card__order, .admin-media-card__drag { position: absolute; top: 8px; display: grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; color: #fff; background: #087f75; font-size: .68rem; font-weight: 800; }
        .admin-media-card__order { right: 8px; }
        .admin-media-card__drag { left: 8px; padding: 0; border: 0; cursor: grab; }
        .admin-media-card__drag:focus { outline: 2px solid #05bda8; outline-offset: 2px; }
        .admin-media-card__remove { display: flex; gap: 5px; align-items: center; padding: 8px 9px; color: #a23b42; background: #fff; font-size: .72rem; font-weight: 750; }
        .admin-media-card__remove input { accent-color: #d34b55; }
        .admin-media-empty { padding: 1.5rem; border: 1px dashed #bfd7d3; border-radius: 11px; color: #779194; text-align: center; font-size: .82rem; }
        .admin-media-empty i { margin-right: .35rem; color: #14a78e; }
        .admin-upload-control { display: flex; gap: .7rem; align-items: center; padding: .8rem; border: 1px dashed #b7d7d1; border-radius: 10px; background: #fff; }
        .admin-upload-control > i { color: #0b9e88; font-size: 1.15rem; }
        .admin-upload-control span { flex: 1; color: #24545a; font-size: .78rem; font-weight: 750; }
        .admin-upload-control small { display: block; margin-top: 2px; color: #789094; font-size: .68rem; font-weight: 500; }
        .admin-upload-control input { width: min(185px, 48%); font-size: .7rem; }
        @media (max-width: 767px) { .content-header { padding-top: 18px; } .card-body { padding: 16px; } .table td, .table thead th { padding-left: 12px; padding-right: 12px; } .admin-upload-control { flex-wrap: wrap; } .admin-upload-control input { width: 100%; } }

        /* Shared modern controls for every admin list, form and action. */
        .content-wrapper .content { padding-bottom: 2rem; }
        .main-header .nav-link { color: #315b60; font-size: .79rem; font-weight: 750; }
        .main-header .nav-link:hover { color: #087d76; }
        .content-wrapper .alert { border: 0; border-radius: 13px; box-shadow: 0 8px 20px rgba(16, 76, 72, .08); }
        .card { transition: box-shadow .2s ease, transform .2s ease; }
        .card:not(.admin-project-form-card):hover { box-shadow: 0 15px 34px rgba(17, 70, 77, .1); }
        .card-header { display: flex; align-items: center; gap: .7rem; }
        .card-header::before { width: 7px; height: 28px; border-radius: 99px; background: linear-gradient(#13b49d, #08756e); content: ''; }
        .card-title { font-size: .98rem; letter-spacing: -.015em; }
        .card-body > .row + .row { margin-top: .2rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group > label { display: flex; align-items: center; gap: .35rem; margin-bottom: .48rem; color: #194e55; letter-spacing: .01em; }
        .form-control, .custom-select { min-height: 45px; border: 1px solid #cddfdd; border-radius: 11px; background: #fcfefe; color: #173f45; font-size: .86rem; transition: border-color .18s ease, box-shadow .18s ease, background .18s ease; }
        textarea.form-control { min-height: 112px; line-height: 1.6; }
        .form-control:hover, .custom-select:hover { border-color: #9bc9c1; background: #fff; }
        .form-control:focus, .custom-select:focus { border-color: #12a58f; background: #fff; box-shadow: 0 0 0 4px rgba(18, 165, 143, .12); }
        .custom-control-label { color: #315c61; font-size: .82rem; font-weight: 750; cursor: pointer; }
        .custom-control-input:checked ~ .custom-control-label::before { border-color: #07877d; background: #07877d; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; min-height: 39px; padding: .55rem .9rem; border: 0; border-radius: 10px; font-size: .78rem; font-weight: 800; line-height: 1; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
        .btn:hover { transform: translateY(-2px); }
        .btn:focus { box-shadow: 0 0 0 4px rgba(13, 154, 137, .15); }
        .btn-teal, .btn-success { color: #fff !important; background: linear-gradient(135deg, #0a9384, #056960); box-shadow: 0 8px 18px rgba(5, 105, 96, .24); }
        .btn-teal:hover, .btn-success:hover { color: #fff !important; background: linear-gradient(135deg, #0eaa96, #05746a); box-shadow: 0 11px 22px rgba(5, 105, 96, .32); }
        .btn-light { border: 1px solid #d4e3e1; background: #fff; color: #41666a; }
        .btn-light:hover { border-color: #9fc7c0; background: #f3faf8; color: #075e58; }
        .btn-outline-teal { border: 1px solid #9fcfc7; background: #effbf8; color: #07766e; }
        .btn-outline-teal:hover { border-color: #087d76; background: #087d76; color: #fff; }
        .btn-warning { color: #654300; background: #ffcb3d; box-shadow: 0 5px 12px rgba(203, 145, 0, .18); }
        .btn-danger { color: #fff; background: linear-gradient(135deg, #ec5e69, #c63a50); box-shadow: 0 5px 12px rgba(178, 43, 63, .16); }
        .btn-xs { min-width: 31px; min-height: 31px; padding: .35rem .45rem; border-radius: 8px; }
        .table-responsive { border-radius: 0 0 16px 16px; }
        .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0 7px; }
        .table thead th { padding: 12px 20px; border: 0; color: #759095; background: transparent; font-size: .65rem; letter-spacing: .1em; }
        .table tbody tr { background: #fff; transition: transform .16s ease, box-shadow .16s ease; }
        .table tbody tr:hover { background: #fbfffe; transform: translateX(3px); box-shadow: 0 5px 15px rgba(11, 72, 76, .05); }
        .table td { border-top: 1px solid #e6f0ee; border-bottom: 1px solid #e6f0ee; padding: 14px 20px; }
        .table td:first-child { border-left: 1px solid #e6f0ee; border-radius: 10px 0 0 10px; }
        .table td:last-child { border-right: 1px solid #e6f0ee; border-radius: 0 10px 10px 0; }
        .badge { padding: .4rem .56rem; border-radius: 99px; font-size: .65rem; font-weight: 800; letter-spacing: .025em; }
        .badge-success { color: #057062; background: #d9f7ef; }.badge-secondary { color: #6b7f83; background: #edf2f2; }.badge-light { color: #477075; background: #edf7f5; }
        .nav-tabs { gap: .25rem; border-bottom: 1px solid #dceae7; }.nav-tabs .nav-link { margin: 0; border: 0; border-radius: 9px 9px 0 0; color: #688489; font-size: .78rem; font-weight: 800; }.nav-tabs .nav-link.active { border-bottom: 3px solid #0ca58f; background: #effaf8; color: #077b72; }
        .tab-content { border: 1px solid #dceae7 !important; border-top: 0 !important; border-radius: 0 0 12px 12px; background: #fcfefe; }
    </style>
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center">
                    <div class="admin-language-switcher" aria-label="{{ __('admin.language') }}">
                        @foreach (['th' => 'ไทย', 'en' => 'EN', 'zh' => '中文'] as $locale => $label)
                            <form method="post" action="{{ route('admin.language', $locale) }}">@csrf<button
                                    type="submit"
                                    class="{{ app()->getLocale() === $locale ? 'is-active' : '' }}">{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}" target="_blank"><i
                            class="fas fa-external-link-alt mr-1"></i> {{ __('admin.view_site') }}</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#"><i
                            class="far fa-user-circle mr-1"></i>{{ session('demo_admin_name', auth()->user()?->name) }}</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item"
                                type="submit"><i
                                    class="fas fa-sign-out-alt mr-2"></i>{{ __('admin.logout') }}</button></form>
                    </div>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('admin.dashboard') }}" class="brand-link"><span class="brand-text font-weight-light">GSB
                    SunPower <strong>ADMIN</strong></span></a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image"><i class="fas fa-user-circle fa-2x text-aqua"></i></div>
                    <div class="info"><a href="#"
                            class="d-block">{{ session('demo_admin_name', auth()->user()?->name) }}</a></div>
                </div>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item"><a href="{{ route('admin.dashboard') }}"
                                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i
                                    class="nav-icon fas fa-th-large"></i>
                                <p>จัดการเว็บไซต์</p>
                            </a></li>
                        <li class="nav-header">{{ __('admin.projects') }}</li>
                        <li class="nav-item"><a href="{{ route('admin.projects', 'ppa') }}"
                                class="nav-link {{ request()->route('type') === 'ppa' ? 'active' : '' }}"><i
                                    class="nav-icon fas fa-solar-panel"></i>
                                <p>{{ __('admin.ppa') }}</p>
                            </a></li>
                        <li class="nav-item"><a href="{{ route('admin.projects', 'epc') }}"
                                class="nav-link {{ request()->route('type') === 'epc' ? 'active' : '' }}"><i
                                    class="nav-icon fas fa-hard-hat"></i>
                                <p>{{ __('admin.epc') }}</p>
                            </a></li>
                        <li class="nav-item"><a href="{{ route('admin.projects', 'ev') }}"
                                class="nav-link {{ request()->route('type') === 'ev' ? 'active' : '' }}"><i
                                    class="nav-icon fas fa-charging-station"></i>
                                <p>{{ __('admin.ev') }}</p>
                            </a></li>
                        <li class="nav-header">{{ __('admin.website_content') }}</li>
                        <li class="nav-item"><a href="{{ route('admin.solutions.index') }}"
                                class="nav-link {{ request()->routeIs('admin.solutions.*') ? 'active' : '' }}"><i
                                    class="nav-icon fas fa-layer-group"></i>
                                <p>{{ __('admin.solutions') }}</p>
                            </a></li>
                        <li class="nav-item"><a href="{{ route('admin.contact-enquiries') }}"
                                class="nav-link {{ request()->routeIs('admin.contact-enquiries') ? 'active' : '' }}"><i
                                    class="nav-icon fas fa-inbox"></i>
                                <p>{{ __('admin.enquiries') }}</p>
                            </a></li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-8">
                            <h1>@yield('heading', __('admin.dashboard'))</h1>
                            @if (request()->routeIs('admin.about-content.*', 'admin.client-logos.*', 'admin.testimonials.*', 'admin.blog.*'))
                                <a class="admin-workspace-back" href="{{ route('admin.dashboard') }}"><i class="fas fa-arrow-left"></i>กลับไปจัดการเว็บไซต์</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible"><button type="button" class="close"
                                data-dismiss="alert">×</button><i
                                class="icon fas fa-check mr-1"></i>{{ session('success') }}</div>
                    @endif @yield('content')
                </div>
            </section>
        </div>
        <footer class="main-footer"><strong>GSB SunPower Group</strong> Admin Panel <span
                class="float-right d-none d-sm-inline-block">Laravel 13 · AdminLTE 3</span></footer>
    </div>
    <script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
