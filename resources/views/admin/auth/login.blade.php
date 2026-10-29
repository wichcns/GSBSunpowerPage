<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบ | GSB SunPower</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
</head>

<body class="hold-transition login-page" style="background:#eef5f4">
    <div class="login-box">
        <div class="text-center mb-2">
            @foreach (['th' => 'ไทย', 'en' => 'EN', 'zh' => '中文'] as $locale => $label)
                <form class="d-inline" method="post" action="{{ route('admin.language', $locale) }}">@csrf<button
                        class="btn btn-sm {{ app()->getLocale() === $locale ? 'btn-teal' : 'btn-default' }}">{{ $label }}</button>
                </form>
            @endforeach
        </div>
        <div class="login-logo"><a href="{{ route('home') }}"><b>GSB SunPower</b><br>Admin</a></div>
        <div class="card card-outline card-teal">
            <div class="card-body login-card-body">
                <p class="login-box-msg">{{ __('admin.sign_in_to_manage') }}</p>
                @if ($errors->any())
                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                @endif
                <form action="{{ route('admin.login.store') }}" method="post">@csrf
                    <div class="input-group mb-3"><input type="email" name="email" class="form-control"
                            placeholder="{{ __('admin.email') }}" value="{{ old('email') }}" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                        </div>
                    </div>
                    <div class="input-group mb-3"><input type="password" name="password" class="form-control"
                            placeholder="{{ __('admin.password') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="icheck-primary"><input type="checkbox" name="remember" id="remember"><label
                                    for="remember">{{ __('admin.remember') }}</label></div>
                        </div>
                        <div class="col-5"><button type="submit"
                                class="btn btn-teal btn-block">{{ __('admin.login') }}</button></div>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-center text-muted small">GSB SunPower Group · Secure administration</p>
    </div>
    <script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
