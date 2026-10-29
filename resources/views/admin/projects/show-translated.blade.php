@extends('admin.layouts.app')
@php($locale = app()->getLocale())
@section('title', $project->{'title_' . $locale} ?: $project->title_th)
@section('heading', __('admin.details'))
@section('content')
    <div class="card card-outline card-teal">
        <div class="card-header">
            <h3 class="card-title">{{ $project->{'title_' . $locale} ?: $project->title_th }}</h3>
            <div class="card-tools"><a class="btn btn-sm btn-warning"
                    href="{{ route('admin.projects.edit', [$type, $project->id]) }}"><i
                        class="fas fa-edit mr-1"></i>{{ __('admin.edit') }}</a></div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if ($project->image_path)
                        <img class="img-fluid rounded mb-3" src="{{ asset($project->image_path) }}" alt="">
                    @endif
                </div>
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-sm-3">{{ $type === 'ppa' ? ($locale === 'th' ? 'มูลค่าโครงการ' : __('admin.investment_value')) : __('admin.capacity') }}</dt>
                        <dd class="col-sm-9">{{ $project->{'scale_' . $locale} ?: $project->scale_th ?: '—' }}</dd>
                        <dt class="col-sm-3">{{ __('admin.configuration') }}</dt>
                        <dd class="col-sm-9">{{ $project->{'configuration_' . $locale} ?: $project->configuration_th ?: '—' }}
                        </dd>
                        <dt class="col-sm-3">{{ __('admin.location') }}</dt>
                        <dd class="col-sm-9">{{ $project->{'location_' . $locale} ?: $project->location_th ?: '—' }}</dd>
                        <dt class="col-sm-3">{{ __('admin.business_model') }}</dt>
                        <dd class="col-sm-9">{{ $project->{'model_' . $locale} ?: $project->model_th ?: '—' }}</dd>
                        <dt class="col-sm-3">{{ __('admin.details') }}</dt>
                        <dd class="col-sm-9">{{ $project->{'overview_' . $locale} ?: $project->overview_th ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="card-footer"><a class="btn btn-default"
                href="{{ route('admin.projects', $type) }}">{{ __('admin_extra.back_to_list') }}</a></div>
    </div>
@endsection
