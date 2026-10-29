@extends('admin.layouts.app')
@section('title', $title)
@section('heading', $title)
@section('content')
    <div class="card card-outline card-teal admin-section-card">
        <div class="card-header">
            <h3 class="card-title"><i class="{{ $icon }} mr-2"></i>{{ $title }}</h3>
            <div class="card-tools">
                @if (($kind ?? '') === 'projects')
                    <a href="{{ route('admin.projects.create', $type) }}" class="admin-add-project"><span><i
                                class="fas fa-plus"></i></span>{{ __('admin.add_project') }}</a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if (($kind ?? '') === 'projects')
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('admin.project') }}</th>
                                <th>中文</th>
                                <th>{{ __('admin.location') }}</th>
                                <th>{{ $type === 'ppa' && app()->getLocale() === 'th' ? 'มูลค่าโครงการ' : ($type === 'ppa' ? __('admin.investment_value') : __('admin.capacity')) }}</th>
                                <th>{{ __('admin.status') }}</th>
                                <th class="text-right">{{ __('admin.manage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ $record->sort_order }}</td>
                                    <td><div class="admin-project-record">
                                            @if ($record->image_path)
                                                <img src="{{ asset($record->image_path) }}" alt="">
                                            @else
                                                <span><i class="fas fa-image"></i></span>
                                            @endif
                                            <div><strong>{{ $record->{'title_' . app()->getLocale()} ?: $record->title_th }}</strong><small>{{ $record->legacy_key }}</small></div>
                                        </div></td>
                                    <td>{{ $record->title_zh }}</td>
                                    <td>{{ $record->{'location_' . app()->getLocale()} ?: $record->location_th }}</td>
                                    <td>{{ $record->{'scale_' . app()->getLocale()} ?: $record->scale_th }}</td>
                                    <td><span class="badge {{ $record->is_published ? 'badge-success' : 'badge-secondary' }}">{{ $record->is_published ? __('admin.published') : __('admin.draft') }}</span></td>
                                    <td class="text-right text-nowrap"><a class="btn btn-xs btn-info"
                                            href="{{ route('admin.projects.show', [$type, $record->id]) }}"
                                            title="{{ __('admin.view') }}" aria-label="{{ __('admin.view') }}"><i
                                                class="fas fa-eye"></i></a> <a class="btn btn-xs btn-warning"
                                            href="{{ route('admin.projects.edit', [$type, $record->id]) }}"
                                            title="{{ __('admin.edit') }}" aria-label="{{ __('admin.edit') }}"><i
                                                class="fas fa-pen"></i></a>
                                        <form class="d-inline" method="post"
                                            action="{{ route('admin.projects.publication', [$type, $record->id]) }}">@csrf
                                            @method('PATCH')<button
                                                class="btn btn-xs {{ $record->is_published ? 'btn-success' : 'btn-secondary' }}"
                                                title="{{ $record->is_published ? 'ซ่อนจากหน้าบ้าน' : 'เผยแพร่บนหน้าบ้าน' }}"
                                                aria-label="{{ $record->is_published ? 'ซ่อนจากหน้าบ้าน' : 'เผยแพร่บนหน้าบ้าน' }}"><i
                                                    class="fas {{ $record->is_published ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button></form>
                                        <form class="d-inline" method="post"
                                            action="{{ route('admin.projects.destroy', [$type, $record->id]) }}"
                                            onsubmit="return confirm('{{ __('admin.delete') }}?')">@csrf
                                            @method('DELETE')<button class="btn btn-xs btn-danger"
                                                title="{{ __('admin.delete') }}" aria-label="{{ __('admin.delete') }}"><i
                                                class="fas fa-trash"></i></button></form>
                                    </td>
                            </tr>@empty<tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ __('admin.content_ready') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>@else<div class="empty-state text-center py-5"><i
                        class="{{ $icon }} fa-3x text-muted mb-3"></i>
                    <h4>{{ __('admin.system_ready') }}</h4>
                    <p class="text-muted mb-0">{{ __('admin.content_ready') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
