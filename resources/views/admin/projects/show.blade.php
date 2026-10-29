@extends('admin.layouts.app')
@section('title', $project->title_th)
@section('heading', 'รายละเอียดโครงการ')
@section('content')
    <div class="card card-outline card-teal">
        <div class="card-header">
            <h3 class="card-title">{{ $project->title_th }}</h3>
            <div class="card-tools"><a class="btn btn-sm btn-warning"
                    href="{{ route('admin.projects.edit', [$type, $project->id]) }}"><i class="fas fa-edit mr-1"></i>แก้ไข</a>
            </div>
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
                        <dt class="col-sm-3">English</dt>
                        <dd class="col-sm-9">{{ $project->title_en ?: '—' }}</dd>
                        <dt class="col-sm-3">中文</dt>
                        <dd class="col-sm-9">{{ $project->title_zh ?: '—' }}</dd>
                        <dt class="col-sm-3">{{ $type === 'ppa' ? 'มูลค่าโครงการ' : 'ขนาด' }}</dt>
                        <dd class="col-sm-9">{{ $project->scale_th ?: '—' }}</dd>
                        <dt class="col-sm-3">รูปแบบ</dt>
                        <dd class="col-sm-9">{{ $project->configuration_th ?: '—' }}</dd>
                        <dt class="col-sm-3">สถานที่</dt>
                        <dd class="col-sm-9">{{ $project->location_th ?: '—' }}</dd>
                        <dt class="col-sm-3">ธุรกิจ</dt>
                        <dd class="col-sm-9">{{ $project->model_th ?: '—' }}</dd>
                        <dt class="col-sm-3">รายละเอียด</dt>
                        <dd class="col-sm-9">{{ $project->overview_th ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="card-footer"><a class="btn btn-default" href="{{ route('admin.projects', $type) }}">กลับไปยังรายการ</a>
        </div>
    </div>
@endsection
