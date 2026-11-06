@extends('crm.layouts.admin')

@section('content')
@php
    $salesMax = max(1, ...array_column($report['sales'], 'count'));
    $engineeringTotal = max(1, array_sum(array_column($report['engineering'], 'count')));
    $palette = ['#0891b2','#2563eb','#7c3aed','#059669','#d97706','#e11d48','#475569','#0f766e'];
    $segments = []; $position = 0; $legendIndex = 0;
    foreach ($report['engineering'] as $stage) {
        if (!$stage['count']) continue;
        $end = $position + ($stage['count'] * 100 / $engineeringTotal);
        $segments[] = $palette[$legendIndex % count($palette)].' '.$position.'% '.$end.'%';
        $position = $end; $legendIndex++;
    }
@endphp
<div class="crm-reports space-y-6">
    <header class="crm-page-intro flex flex-wrap items-end justify-between gap-4">
        <div><p class="crm-eyebrow">{{ __('crm_headings.reports') }}</p><h1>{{ __('reports.title') }}</h1><p>{{ $report['scope'] === 'staff_all' ? __('reports.staff_scope') : __('reports.organization_scope') }}</p></div>
        <a href="{{ route('crm.admin.reports.export', request()->query()) }}" class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm">{{ __('reports.download') }}</a>
    </header>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('crm.admin.reports.index') }}" class="grid gap-3 bg-slate-50 p-5 sm:grid-cols-2 xl:grid-cols-7">
            <label><span class="crm-field-label">{{ __('reports.owner') }}</span><select name="owner" class="crm-site-input"><option value="">{{ __('reports.all') }}</option>@foreach($report['options']['owners'] as $id => $name)<option value="{{ $id }}" @selected(($filters['owner'] ?? '') == $id)>{{ $name }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('reports.area') }}</span><select name="area" class="crm-site-input"><option value="">{{ __('reports.all') }}</option>@foreach($report['options']['areas'] as $value)<option @selected(($filters['area'] ?? '') === $value)>{{ $value }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('reports.location_type') }}</span><select name="location_type" class="crm-site-input"><option value="">{{ __('reports.all') }}</option>@foreach($report['options']['location_types'] as $value)<option @selected(($filters['location_type'] ?? '') === $value)>{{ $value }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('reports.sales_stage') }}</span><select name="sales_stage" class="crm-site-input"><option value="">{{ __('reports.all') }}</option>@foreach($salesStages as $code => $label)<option value="{{ $code }}" @selected(($filters['sales_stage'] ?? '') === $code)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('reports.engineering_stage') }}</span><select name="engineering_stage" class="crm-site-input"><option value="">{{ __('reports.all') }}</option>@foreach($engineeringStages as $code => $label)<option value="{{ $code }}" @selected(($filters['engineering_stage'] ?? '') === $code)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('reports.from') }}</span><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="crm-site-input"></label>
            <label><span class="crm-field-label">{{ __('reports.to') }}</span><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="crm-site-input"></label>
            <div class="flex items-end gap-3 sm:col-span-2 xl:col-span-7"><button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white">{{ __('reports.show') }}</button><a href="{{ route('crm.admin.reports.index') }}" class="px-2 py-2.5 text-sm text-slate-600">{{ __('reports.clear') }}</a><span class="ml-auto text-xs text-slate-500">{{ __('reports.date_note') }}</span></div>
        </form>
    </section>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6" aria-label="{{ __('reports.metrics') }}">
        @foreach([
            [__('reports.sites'),'sites','border-cyan-200 bg-cyan-50/70 text-cyan-800'],
            [__('reports.active'),'active','border-blue-200 bg-blue-50/70 text-blue-800'],
            [__('reports.signed'),'signed','border-violet-200 bg-violet-50/70 text-violet-800'],
            [__('reports.construction'),'construction','border-amber-200 bg-amber-50/70 text-amber-800'],
            [__('reports.live'),'live','border-emerald-200 bg-emerald-50/70 text-emerald-800'],
            [__('reports.overdue'),'overdue','border-rose-200 bg-rose-50/70 text-rose-800']
        ] as [$label,$key,$tone])
            <article class="rounded-2xl border p-5 shadow-sm {{ $tone }}"><p class="text-sm font-semibold">{{ $label }}</p><p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($report['kpis'][$key]) }}</p></article>
        @endforeach
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-label="{{ __('reports.location_counts') }}">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><h2 class="font-bold text-slate-950">{{ __('reports.location_counts') }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('reports.location_note') }}</p></div>
            <p class="text-sm font-semibold text-cyan-900">{{ __('reports.sites') }}: {{ number_format($report['kpis']['sites']) }}</p>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            @forelse($report['locations'] as $area => $count)
                <span class="rounded-xl border border-cyan-100 bg-cyan-50 px-3 py-2 text-sm text-cyan-950">{{ $area }} <strong class="ml-2 tabular-nums">{{ number_format($count) }}</strong></span>
            @empty
                <p class="text-sm text-slate-500">{{ __('reports.no_locations') }}</p>
            @endforelse
        </div>
    </section>
    <section class="grid gap-5 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-label="{{ __('reports.sales_chart') }}"><h2 class="text-lg font-bold text-slate-950">{{ __('reports.sales_stage') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('reports.sales_description') }}</p><div class="mt-5 space-y-3">@foreach($report['sales'] as $stage)<div class="grid grid-cols-[minmax(8rem,14rem)_1fr_2.5rem] items-center gap-3"><span class="truncate text-sm text-slate-700">{{ $stage['label'] }}</span><span class="h-3 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600" style="width: {{ $stage['count'] * 100 / $salesMax }}%"></span></span><strong class="text-right tabular-nums">{{ $stage['count'] }}</strong></div>@endforeach</div></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-bold text-slate-950">{{ __('reports.engineering_ratio') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('reports.engineering_description') }}</p><div class="mt-5 grid items-center gap-6 sm:grid-cols-[11rem_1fr]"><div class="mx-auto grid h-40 w-40 place-items-center rounded-full" role="img" aria-label="{{ __('reports.engineering_chart') }}" style="background: conic-gradient({{ $segments ? implode(',', $segments) : '#e2e8f0 0 100%' }})"><span class="grid h-24 w-24 place-items-center rounded-full bg-white text-center"><strong class="text-2xl">{{ array_sum(array_column($report['engineering'], 'count')) }}</strong><small class="text-slate-500">{{ __('reports.projects') }}</small></span></div><div class="space-y-2">@php $legendIndex=0; @endphp @foreach($report['engineering'] as $stage)<div class="flex items-center gap-2 text-sm"><span class="h-3 w-3 rounded-full" style="background: {{ $palette[$legendIndex % count($palette)] }}"></span><span class="flex-1 text-slate-700">{{ $stage['label'] }}</span><strong>{{ $stage['count'] }}</strong></div>@php $legendIndex++; @endphp @endforeach</div></div></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">{{ __('reports.workload') }}</h2><div class="mt-5 space-y-3">@forelse($report['workload'] as $name => $count)<div class="flex items-center gap-3"><span class="min-w-0 flex-1 truncate text-sm text-slate-700">{{ $name }}</span><span class="h-2 w-2 rounded-full bg-teal-500"></span><strong class="tabular-nums">{{ $count }}</strong></div>@empty<p class="text-sm text-slate-500">{{ __('reports.no_owner_data') }}</p>@endforelse</div></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">{{ __('reports.contract_status') }}</h2><div class="mt-5 grid grid-cols-2 gap-3">@foreach([[__('reports.complete'),'complete','bg-emerald-50 text-emerald-800'],[__('reports.incomplete'),'incomplete','bg-amber-50 text-amber-800'],[__('reports.near_expiry'),'next_90','bg-orange-50 text-orange-800'],[__('reports.expired'),'expired','bg-rose-50 text-rose-800']] as [$label,$key,$tone])<div class="rounded-xl p-4 {{ $tone }}"><p class="text-sm">{{ $label }}</p><strong class="mt-2 block text-2xl text-slate-950">{{ $report['contract'][$key] }}</strong></div>@endforeach</div></article>
    </section>
    <section class="grid gap-5 xl:grid-cols-2">
        @foreach([[__('reports.attention_overdue'),'overdue',__('reports.empty_overdue')],[__('reports.attention_contracts'),'contracts',__('reports.empty_contracts')],[__('reports.attention_engineering'),'engineering_unassigned',__('reports.empty_engineering')],[__('reports.attention_incomplete'),'incomplete',__('reports.empty_incomplete')]] as [$title,$key,$empty])
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><header class="border-b border-slate-200 p-5"><h2 class="font-bold">{{ $title }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('reports.items', ['count' => count($report['attention'][$key])]) }}</p></header><div class="divide-y divide-slate-100">@forelse(array_slice($report['attention'][$key],0,8) as $row)<div class="flex items-center justify-between gap-4 p-4"><div><strong class="text-sm text-slate-900">{{ $row['site_name'] }}</strong><p class="mt-1 text-xs text-slate-500">{{ $row['owner_name'] ?: __('reports.unassigned') }} · {{ $row['area'] ?: __('reports.unspecified_area') }}</p></div><a href="{{ route('crm.admin.pipeline.show', $row['source_id']) }}" class="text-sm font-semibold text-cyan-700">{{ __('reports.view') }}</a></div>@empty<p class="p-8 text-center text-sm text-slate-500">{{ $empty }}</p>@endforelse</div></article>
        @endforeach
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="{{ __('reports.progress_detail') }}">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold text-slate-950">{{ __('reports.progress_detail') }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('reports.items', ['count' => $records->total()]) }}</p></div>
        <div class="overflow-x-auto crm-mobile-table">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs text-slate-600"><tr>
                    <th class="px-4 py-3">{{ __('reports.site') }}</th><th class="px-4 py-3">{{ __('reports.area') }}</th><th class="px-4 py-3">{{ __('reports.owner') }}</th><th class="px-4 py-3">{{ __('reports.sales_stage') }}</th><th class="px-4 py-3">{{ __('reports.engineering_stage') }}</th><th class="px-4 py-3">{{ __('reports.next_action') }}</th><th class="px-4 py-3">{{ __('reports.due_date') }}</th><th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $row)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-950" data-label="{{ __('reports.site') }}">{{ $row['site_name'] }}</td>
                            <td class="px-4 py-3" data-label="{{ __('reports.area') }}">{{ $row['area'] ?: __('reports.unspecified_area') }}</td>
                            <td class="px-4 py-3" data-label="{{ __('reports.owner') }}">{{ $row['owner_name'] ?: __('reports.unassigned') }}</td>
                            <td class="px-4 py-3" data-label="{{ __('reports.sales_stage') }}">{{ $row['sales_stage_label'] }}</td>
                            <td class="px-4 py-3" data-label="{{ __('reports.engineering_stage') }}">{{ $row['engineering_stage_label'] }}</td>
                            <td class="px-4 py-3" data-label="{{ __('reports.next_action') }}">{{ $row['next_action'] ?: '—' }}</td>
                            <td class="px-4 py-3" data-label="{{ __('reports.due_date') }}">{{ $row['next_action_at'] ?: '—' }}</td>
                            <td class="px-4 py-3"><a href="{{ route('crm.admin.pipeline.index', ['open' => $row['source_id']]) }}" class="font-semibold text-cyan-800">{{ __('reports.view') }} ↗</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">{{ __('reports.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $records->links() }}</div>@endif
    </section>
</div>
@endsection


