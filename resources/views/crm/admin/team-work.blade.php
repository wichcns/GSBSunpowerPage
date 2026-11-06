@extends('crm.layouts.admin')

@section('content')
<div class="space-y-6">
    <header class="crm-page-intro">
        <p class="crm-eyebrow">{{ __('crm_headings.team_work') }}</p>
        <h1>{{ __('team_work.title') }}</h1>
        <p>{{ __('team_work.description') }}</p>
    </header>

    <p class="rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">
        {{ __('team_work.scope_note') }}
    </p>

    <form method="GET" action="{{ route('crm.admin.team-work') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="min-w-56 flex-1"><span class="crm-field-label">{{ __('team_work.assignee') }}</span>
            <select name="staff" class="crm-site-input" onchange="this.form.submit()">
                <option value="">{{ __('team_work.all_staff') }}</option>
                @foreach($staff as $person)
                    <option value="{{ $person['user']->id }}" @selected($selectedStaff === $person['user']->id)>{{ $person['user']->name }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="min-h-11 rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white">{{ __('team_work.view_work') }}</button>
    </form>

    @php
        $visible = array_values(array_filter($staff, fn (array $person) => $selectedStaff === null || $person['user']->id === $selectedStaff));
        $siteCount = array_sum(array_map(fn (array $person) => count($person['sites']), $visible));
        $taskCount = array_sum(array_map(fn (array $person) => count($person['items']), $visible));
        $overdueCount = array_sum(array_column($visible, 'overdue'));
    @endphp
    <section aria-label="{{ __('team_work.summary') }}" class="grid gap-3 sm:grid-cols-3">
        @foreach([[__('team_work.assigned_sites'), $siteCount], [__('team_work.tasks'), $taskCount], [__('team_work.overdue'), $overdueCount]] as [$label, $count])
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-600">{{ $label }}</p><p class="mt-1 text-3xl font-bold tabular-nums text-slate-950">{{ $count }}</p></div>
        @endforeach
    </section>

    @forelse($visible as $person)
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="{{ __('team_work.staff_work', ['name' => $person['user']->name]) }}">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div><h2 class="font-bold text-slate-950">{{ $person['user']->name }}</h2><p class="text-xs text-slate-500">Staff · {{ $person['user']->email }}</p></div>
                <p class="text-sm text-slate-600">{{ __('team_work.staff_counts', ['sites' => count($person['sites']), 'tasks' => count($person['items']), 'overdue' => $person['overdue']]) }}</p>
            </div>
            <div class="grid gap-5 p-5 lg:grid-cols-2">
                <div><h3 class="font-semibold text-slate-900">{{ __('team_work.assigned_sites') }}</h3>
                    @forelse($person['sites'] as $site)
                        <p class="mt-3 rounded-xl border border-slate-100 p-3 text-sm text-slate-800">{{ $site['name'] }} <span class="block text-xs text-slate-500">{{ $site['site_code'] ?: __('team_work.no_site_code') }}</span></p>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">{{ __('team_work.no_sites') }}</p>
                    @endforelse
                </div>
                <div><h3 class="font-semibold text-slate-900">{{ __('team_work.tasks') }}</h3>
                    @forelse($person['items'] as $item)
                        <div class="mt-3 rounded-xl border border-slate-100 p-3 text-sm"><p class="font-semibold text-slate-900">{{ $item['title'] }}</p><p class="mt-1 text-slate-600">{{ $item['site_name'] }} · {{ __('team_work.due', ['date' => $item['due_label']]) }}</p><a href="{{ $item['url'] }}" class="mt-2 inline-block font-semibold text-cyan-800 hover:underline">{{ __('team_work.view_details') }} ↗</a></div>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">{{ __('team_work.no_tasks') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    @empty
        <p class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">{{ __('team_work.no_staff') }}</p>
    @endforelse
</div>
@endsection
