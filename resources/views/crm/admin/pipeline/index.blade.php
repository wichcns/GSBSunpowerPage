@extends('crm.layouts.admin')

@section('content')
<div class="crm-pipeline"
    x-data="pipelineDirectory(@js(route('crm.admin.pipeline.index')), @js(route('crm.admin.pipeline.index', ['section' => 'sites'])), @js(__('pipeline_js')))"
    @keydown.escape.window="close()">
    <section class="crm-page-intro flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="crm-eyebrow">BUSINESS DEVELOPMENT</p>
            <h1>{{ __('pipeline_page.title') }}</h1>
            <p>{{ __('pipeline_page.description') }}</p>
        </div>
        <div class="inline-flex items-center gap-3 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5"
            aria-label="{{ __('pipeline_page.total_aria', ['count' => $summary['total']]) }}"><span
                class="text-xl font-bold tabular-nums leading-none text-slate-950">{{ $summary['total'] }}</span><span
                class="whitespace-nowrap text-xs font-medium text-cyan-800">{{ __('pipeline_page.all_opportunities') }}</span></div>
    </section>
    <nav class="mb-5 flex flex-wrap gap-2" aria-label="{{ __('pipeline_page.title') }}">
        <a href="{{ route('crm.admin.pipeline.index', ['view' => $viewMode]) }}" @if($section === 'sales') aria-current="page" @endif class="rounded-xl px-4 py-2.5 text-sm font-semibold {{ $section === 'sales' ? 'bg-cyan-700 text-white' : 'border border-slate-200 bg-white text-slate-700' }}">{{ __('pipeline_page.all_opportunities') }}</a>
        @if(auth()->user()->hasPermission('sites.view'))
            <a href="{{ route('crm.admin.pipeline.index', ['section' => 'sites']) }}" @if($section === 'sites') aria-current="page" @endif class="rounded-xl px-4 py-2.5 text-sm font-semibold {{ $section === 'sites' ? 'bg-cyan-700 text-white' : 'border border-slate-200 bg-white text-slate-700' }}">{{ __('sites.title') }}</a>
        @endif
    </nav>
    @if($section === 'sites')
        @include('crm.admin.sites.partials.directory', ['embedded' => true])
    @else
    @if($summary['unmatched'])
        <div role="alert" class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
    {{ __('pipeline_page.unmatched_alert', ['count' => $summary['unmatched']]) }}</div>@endif
    @php($groups = [
        ['early', __('pipeline_page.groups.early'), 'bg-sky-500', 'bg-sky-50 text-sky-700'],
        ['review_mou', __('pipeline_page.groups.review_mou'), 'bg-violet-500', 'bg-violet-50 text-violet-700'],
        ['contract_signed', __('pipeline_page.groups.contract_signed'), 'bg-emerald-500', 'bg-emerald-50 text-emerald-700'],
        ['paused_closed', __('pipeline_page.groups.paused_closed'), 'bg-amber-500', 'bg-amber-50 text-amber-700'],
    ])
    <section aria-label="{{ __('pipeline_page.summary') }}" class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($groups as [$key, $label, $barTone, $labelTone])
        @php($count = $summary['groups'][$key])
        @php($share = $summary['total'] > 0 ? round($count / $summary['total'] * 100) : 0)
        <article
            class="relative flex min-h-40 min-w-0 flex-col items-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
            <div class="absolute inset-x-0 top-0 h-1 {{ $barTone }}" aria-hidden="true"></div>
            <h2
                class="inline-flex min-h-8 max-w-full items-center justify-center rounded-lg px-2.5 py-1 text-center text-sm font-semibold leading-5 {{ $labelTone }}">
                {{ $label }}
            </h2>
            <div class="mt-auto pt-5 text-4xl font-bold tabular-nums leading-none tracking-tight text-slate-950">
                {{ $count }}
            </div>
            <div class="mt-5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                <div class="h-full rounded-full {{ $barTone }}" style="width: {{ $share }}%"></div>
            </div>
        </article>
        @endforeach
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-950">{{ __('pipeline_page.list') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('pipeline_page.view_hint') }}</p>
            </div>
            <nav aria-label="{{ __('pipeline_page.view_type') }}"
                class="inline-flex rounded-xl border border-slate-200 bg-slate-100 p-1 text-sm font-semibold">
                <a href="{{ route('crm.admin.pipeline.index', array_filter(array_merge(request()->except('page'), ['view' => 'table']), fn($value) => $value !== '' && $value !== null)) }}"
                    @if($viewMode === 'table') aria-current="page" @endif
                    class="rounded-lg px-4 py-2 transition {{ $viewMode === 'table' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-950' }}">☷
                    {{ __('pipeline_page.table') }}</a>
                <a href="{{ route('crm.admin.pipeline.index', array_filter(array_merge(request()->except('page'), ['view' => 'board']), fn($value) => $value !== '' && $value !== null)) }}"
                    @if($viewMode === 'board') aria-current="page" @endif
                    class="rounded-lg px-4 py-2 transition {{ $viewMode === 'board' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-950' }}">▦
                    {{ __('pipeline_page.board') }}</a>
            </nav>
        </div>
        <form method="GET" action="{{ route('crm.admin.pipeline.index') }}"
            class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-5 sm:grid-cols-2 xl:grid-cols-5">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            <label class="xl:col-span-2"><span
                    class="mb-1 block text-xs font-semibold text-slate-600">{{ __('pipeline_page.search') }}</span><input name="q" maxlength="100"
                    value="{{ $filters['search'] }}" placeholder="{{ __('pipeline_page.search_placeholder') }}"
                    class="w-full rounded-xl border-slate-200 text-sm"></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('pipeline_page.stage') }}</span><select name="stage"
                    class="w-full rounded-xl border-slate-200 text-sm">
                    <option value="">{{ __('pipeline_page.all') }}</option>@foreach($stages as $code => $label)
                        <option value="{{ $code }}" @selected($filters['stage'] === $code)>{{ $label }}
                    ({{ $summary['stages'][$code] ?? 0 }})</option>@endforeach
                </select></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('pipeline_page.priority') }}</span><select name="priority"
                    class="w-full rounded-xl border-slate-200 text-sm">
                    <option value="">{{ __('pipeline_page.all') }}</option>@foreach($priorities as $code => $label)
                    <option value="{{ $code }}" @selected($filters['priority'] === $code)>{{ $label }}</option>@endforeach
                </select></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('pipeline_page.owner') }}</span><select name="owner"
                    class="w-full rounded-xl border-slate-200 text-sm">
                    <option value="">{{ __('pipeline_page.all') }}</option>@foreach($owners as $owner)
                    <option value="{{ $owner }}" @selected($filters['owner'] === $owner)>{{ $owner }}</option>@endforeach
                </select></label>
            <div class="flex items-end gap-3 xl:col-span-5"><button
                    class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('pipeline_page.search') }}</button><a
                    href="{{ route('crm.admin.pipeline.index', ['view' => $viewMode]) }}"
                    class="px-2 py-2.5 text-sm text-slate-500">{{ __('pipeline_page.clear_filters') }}</a></div>
        </form>
        <div class="border-b border-slate-100 px-5 py-3 text-xs text-slate-500">{{ __('pipeline_page.found', ['count' => $rows->total()]) }}</div>
        @if($viewMode === 'table')
            <div class="hidden overflow-x-auto xl:block">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">{{ __('pipeline_page.site') }}</th>
                            <th class="px-5 py-3">{{ __('pipeline_page.stage') }}</th>
                            <th class="px-5 py-3">{{ __('pipeline_page.priority') }}</th>
                            <th class="px-5 py-3">{{ __('business_summary.heading') }}</th>
                            <th class="px-5 py-3">{{ __('pipeline_page.next_action') }}</th>
                            <th class="px-5 py-3">{{ __('pipeline_page.owner') }}</th>
                            <th class="px-5 py-3">{{ __('pipeline_page.view') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
                            <tr class="hover:bg-cyan-50/40">
                                <td class="px-5 py-4"><button type="button"
                                        class="font-semibold text-slate-950 hover:text-cyan-700"
                                        @click="open(@js($row['source_id']), $event.currentTarget)">{{ $row['site_name'] }}</button>
                                    <div class="mt-1 text-xs text-slate-400">{{ $row['site_code'] ?? $row['source_id'] }} ·
                                        {{ $row['area'] ?: __('pipeline_page.no_area') }}
                                    </div>@if($row['site_id'])<a
                                        href="{{ route('crm.admin.pipeline.index', ['section' => 'sites', 'site' => $row['site_id']]) }}"
                                    class="mt-1 inline-block text-xs font-medium text-cyan-700">{{ __('pipeline_page.view_site') }}</a>@endif
                                    @if($row['unmatched'])
                                    <div class="mt-1 text-xs text-amber-700">{{ __('pipeline_page.sync_pending') }}</div>@endif
                                </td>
                                <td class="px-5 py-4"><span
                                        class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-800">{{ $row['stage_label'] }}</span>
                                </td>
                                <td class="px-5 py-4">{{ $row['priority_label'] }}</td>
                                <td class="px-5 py-4">{{ $row['commercial_model'] ? __('business_summary.'.$row['commercial_model']) : __('pipeline_status.unspecified') }} · {{ is_numeric($row['rental_cost']) ? number_format((float) $row['rental_cost']).($row['rental_unit'] ? ' / '.$row['rental_unit'] : '') : __('pipeline_status.unspecified') }} · {{ $row['parking_spaces'] ?? __('pipeline_status.unspecified') }}</td>
                                <td class="max-w-xs truncate px-5 py-4">{{ $row['next_action'] }}</td>
                                <td class="px-5 py-4">{{ $row['owner'] }}</td>
                                <td class="px-5 py-4"><button type="button"
                                        @click="open(@js($row['source_id']), $event.currentTarget)"
                                        aria-label="{{ __('pipeline_page.view_details', ['name' => $row['site_name']]) }}" title="{{ __('pipeline_labels.view_title') }}"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 text-cyan-700 shadow-sm transition hover:border-cyan-400 hover:bg-cyan-600 hover:text-white hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600"><svg
                                            aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round" class="h-5 w-5">
                                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg></button></td>
                            </tr>
                        @empty<tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">{{ __('pipeline_page.no_matches') }}</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="divide-y divide-slate-100 xl:hidden">@forelse($rows as $row)<button type="button"
                class="w-full p-5 text-left hover:bg-cyan-50/40"
                @click="open(@js($row['source_id']), $event.currentTarget)">
                <div class="flex justify-between gap-3 font-semibold text-slate-950">
                    <span>{{ $row['site_name'] }}</span><span class="text-cyan-700">↗</span>
                </div>
                <div class="mt-1 text-xs text-slate-500">{{ $row['area'] ?: __('pipeline_page.no_area') }} ·
                    {{ $row['site_code'] ?? $row['source_id'] }}
                </div>
                <div class="mt-3 text-sm text-slate-700">{{ $row['stage_label'] }} · {{ $row['commercial_model'] ? __('business_summary.'.$row['commercial_model']) : __('pipeline_status.unspecified') }} · {{ is_numeric($row['rental_cost']) ? number_format((float) $row['rental_cost']).($row['rental_unit'] ? ' / '.$row['rental_unit'] : '') : __('pipeline_status.unspecified') }} · {{ $row['parking_spaces'] ?? __('pipeline_status.unspecified') }}
                </div>
                <div class="mt-2 text-sm text-slate-600">{{ __('pipeline_page.next_action_prefix') }} {{ $row['next_action'] }}</div>@if($row['unmatched'])
                <div class="mt-2 text-xs text-amber-700">{{ __('pipeline_page.sync_pending') }}</div>@endif
            </button>@empty<p class="p-10 text-center text-slate-500">{{ __('pipeline_page.no_matches') }}</p>@endforelse</div>
            @if($rows->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $rows->links() }}</div>@endif
        @else
        @php($boardColumns = [
            ['early', __('pipeline_page.groups.early'), 'bg-sky-500'],
            ['review_mou', __('pipeline_page.groups.review_mou'), 'bg-violet-500'],
            ['contract_signed', __('pipeline_page.groups.contract_signed'), 'bg-emerald-500'],
            ['paused_closed', __('pipeline_page.groups.paused_closed'), 'bg-amber-500'],
            ['other', __('pipeline_page.groups.other'), 'bg-slate-500'],
        ])
        <div class="grid gap-4 bg-slate-50/70 p-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('pipeline_page.board_label') }}">
            @foreach($boardColumns as [$key, $label, $marker])
                @if($key !== 'other' || count($boardGroups[$key]))
                    <section class="min-w-0 rounded-2xl border border-slate-200 bg-slate-100/70 p-3" aria-label="{{ $label }}">
                        <div class="mb-3 flex items-center justify-between gap-2 px-1">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800"><span
                                    class="h-2.5 w-2.5 shrink-0 rounded-full {{ $marker }}"></span>{{ $label }}</h3><span
                                class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-700">{{ count($boardGroups[$key]) }}</span>
                        </div>
                        <div class="space-y-3">
                            @forelse($boardGroups[$key] as $row)
                                <button type="button" @click="open(@js($row['source_id']), $event.currentTarget)"
                                    class="w-full rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600"
                                    aria-label="{{ __('pipeline_page.view_details', ['name' => $row['site_name']]) }}">
                                    <span
                                        class="block break-words text-sm font-semibold text-slate-950">{{ $row['site_name'] }}</span>
                                    <span class="mt-1 block truncate text-xs text-slate-500">{{ $row['area'] ?: __('pipeline_page.no_area') }}
                                        · {{ $row['owner'] }}</span>
                                    <span class="mt-3 flex items-center justify-between gap-2"><span
                                            class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-800">{{ $row['stage_label'] }}</span><span
                                            class="text-xs font-semibold text-slate-600">{{ $row['commercial_model'] ? __('business_summary.'.$row['commercial_model']) : __('pipeline_status.unspecified') }} · {{ is_numeric($row['rental_cost']) ? number_format((float) $row['rental_cost']).($row['rental_unit'] ? ' / '.$row['rental_unit'] : '') : __('pipeline_status.unspecified') }} · {{ $row['parking_spaces'] ?? __('pipeline_status.unspecified') }}</span></span>
                                    <span class="mt-3 block border-t border-slate-100 pt-3 text-xs text-slate-600">{{ __('pipeline_page.next_action_prefix') }} <span
                                            class="font-medium text-slate-800">{{ $row['next_action'] }}</span></span>
                                    @if($row['unmatched'])<span
                                    class="mt-2 block text-xs font-medium text-amber-700">{{ __('pipeline_page.sync_pending') }}</span>@endif
                                </button>
                            @empty<p
                                class="rounded-xl border border-dashed border-slate-300 bg-white/70 px-4 py-6 text-center text-xs text-slate-500">
                            {{ __('pipeline_page.empty_group') }}</p>@endforelse
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
        @endif
    </section>
    @include('crm.admin.pipeline.partials.detail-drawer')
    @endif
</div>
@endsection
