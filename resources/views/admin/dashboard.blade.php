@extends('admin.layouts.app')
@section('title', __('admin.dashboard'))
@section('heading', __('admin_extra.website_dashboard'))
@section('content')
    <div class="admin-dashboard-intro d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div><p class="mb-1 text-uppercase font-weight-bold small" style="letter-spacing:.12em;color:#07877d;">WEBSITE WORKSPACE</p><p class="mb-0 text-muted">ติดตามเนื้อหา โครงการ และข้อความติดต่อจากหน้าเว็บไซต์</p></div>
        <a href="{{ route('admin.website-content') }}" class="admin-add-project mt-3 mt-md-0"><span><i class="fas fa-th-large"></i></span>จัดการหน้าบ้าน</a>
    </div>
    <div class="row admin-dashboard-metrics">
        @foreach ($summary as $item)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-{{ $item['colour'] }}">
                    <div class="inner">
                        <h3>{{ $item['value'] }}</h3>
                        <p>{{ $item['label'] }}</p>
                    </div>
                    <div class="icon"><i class="{{ $item['icon'] }}"></i></div><a
                        href="{{ isset($item['type']) ? route($item['route'], $item['type']) : route($item['route']) }}"
                        class="small-box-footer">{{ __('admin.manage') }} <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-teal">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin_extra.getting_started') }}</h3>
                </div>
                <div class="card-body">
                    <p>{{ __('admin_extra.intro') }}</p>
                    <ol class="mb-0">
                        <li>{{ __('admin_extra.step_one') }}</li>
                        <li>{{ __('admin_extra.step_two') }}</li>
                        <li>{{ __('admin_extra.step_three') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.system_ready') }}</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><i
                            class="fas fa-check-circle text-success mr-2"></i>{{ __('admin_extra.laravel_ready') }}</p>
                    <p class="mb-0"><i
                            class="fas fa-check-circle text-success mr-2"></i>{{ __('admin_extra.adminlte_ready') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
