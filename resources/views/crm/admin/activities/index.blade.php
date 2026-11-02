@extends('crm.layouts.admin')

@section('content')
<div class="space-y-6" x-data="myWorkDetails(@js(route('crm.admin.pipeline.index')))" @keydown.escape.window="close()">
    <header class="crm-page-intro flex flex-wrap items-end justify-between gap-4">
        <div><p class="crm-eyebrow">TEAM ACTIVITY · FOLLOW-UP</p><h1>{{ __('activities.title') }}</h1><p>{{ __('activities.description') }}</p></div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('ownership.manage'))
                <a data-testid="activity-owner-assignment" href="{{ route('crm.admin.ownership.index') }}" class="rounded-xl border border-cyan-200 bg-white px-5 py-3 text-sm font-semibold text-cyan-800 hover:bg-cyan-50">{{ __('ownership.title') }} ↗</a>
            @endif
            <button type="button" class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm" @click="open = true" onclick="document.getElementById('activity-directory')?.scrollIntoView({behavior:'smooth'})">{{ __('activities.latest') }}</button>
        </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('activities.summary') }}">
        @foreach([
            [__('activities.total'), $summary['total'], 'border-cyan-200 bg-cyan-50/60 text-cyan-800'],
            [__('activities.today'), $summary['today'], 'border-blue-200 bg-blue-50/60 text-blue-800'],
            [__('activities.overdue'), $summary['overdue'], 'border-rose-200 bg-rose-50/60 text-rose-800'],
            [__('activities.undated'), $summary['undated'], 'border-amber-200 bg-amber-50/60 text-amber-800'],
        ] as [$label, $value, $tone])
            <article class="rounded-2xl border p-5 shadow-sm {{ $tone }}"><p class="text-sm font-semibold">{{ $label }}</p><p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($value) }}</p></article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('crm.admin.activities.index') }}" class="grid gap-3 border-b border-slate-200 bg-slate-50 p-5 md:grid-cols-2 xl:grid-cols-6">
            <label class="xl:col-span-2"><span class="crm-field-label">{{ __('activities.search') }}</span><input name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="{{ __('activities.search_placeholder') }}" class="crm-site-input"></label>
            <label><span class="crm-field-label">{{ __('activities.type') }}</span><select name="type" class="crm-site-input"><option value="">{{ __('activities.all') }}</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('activities.owner') }}</span><select name="owner" class="crm-site-input"><option value="">{{ __('activities.all') }}</option>@foreach($owners as $owner)<option value="{{ $owner->id }}" @selected(($filters['owner'] ?? '') == $owner->id)>{{ $owner->name }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('activities.due') }}</span><select name="due" class="crm-site-input"><option value="">{{ __('activities.all') }}</option><option value="today" @selected(($filters['due'] ?? '') === 'today')>{{ __('activities.today') }}</option><option value="overdue" @selected(($filters['due'] ?? '') === 'overdue')>{{ __('activities.overdue') }}</option><option value="upcoming" @selected(($filters['due'] ?? '') === 'upcoming')>{{ __('activities.upcoming') }}</option><option value="undated" @selected(($filters['due'] ?? '') === 'undated')>{{ __('activities.undated') }}</option></select></label>
            <div class="grid grid-cols-2 gap-2"><label><span class="crm-field-label">{{ __('activities.date_from') }}</span><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="crm-site-input"></label><label><span class="crm-field-label">{{ __('activities.date_to') }}</span><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="crm-site-input"></label></div>
            <div class="flex items-end gap-3 md:col-span-2 xl:col-span-6"><button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('activities.search') }}</button><a href="{{ route('crm.admin.activities.index') }}" class="px-2 py-2.5 text-sm text-slate-600">{{ __('activities.clear') }}</a></div>
        </form>

        <div id="activity-directory" class="divide-y divide-slate-100">
            @forelse($items as $item)
                <article class="grid gap-4 p-5 hover:bg-cyan-50/30 lg:grid-cols-[10rem_minmax(0,1fr)_12rem_auto] lg:items-center" data-visit-endpoint="{{ $item['visit_url'] }}">
                    <div><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $item['type'] === 'NEXT_ACTION' ? 'bg-amber-100 text-amber-800' : ($item['origin'] === 'current' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700') }}">{{ $item['type_label'] }}</span><p class="mt-2 text-xs text-slate-500">{{ $item['origin_label'] }}</p></div>
                    <div class="min-w-0"><h2 class="font-bold text-slate-950">{{ $item['site_name'] }}</h2><p class="mt-1 break-words text-sm text-slate-700">{{ $item['title'] }}</p><p class="mt-2 text-xs text-slate-500">{{ __('activities.owner_label', ['name' => $item['owner_name']]) }}</p></div>
                    <div><p class="text-sm font-semibold {{ $item['due_status'] === 'overdue' ? 'text-rose-700' : 'text-slate-800' }}">{{ $item['occurred_label'] }}</p>@if($item['due_status'] === 'overdue')<p class="mt-1 text-xs font-semibold text-rose-600">{{ __('activities.overdue') }}</p>@elseif($item['due_status'] === 'today')<p class="mt-1 text-xs font-semibold text-blue-700">{{ __('activities.due_today') }}</p>@endif</div>
                    <div class="flex flex-wrap gap-2"><button type="button" class="rounded-xl border border-cyan-200 bg-white px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-50" @click="openSite(@js($item['source_id']))">{{ __('activities.view') }}</button><a href="{{ $item['pipeline_url'] }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('activities.open_pipeline') }} ↗</a></div>
                </article>
            @empty
                <div class="p-12 text-center"><p class="font-semibold text-slate-900">{{ __('activities.empty') }}</p><p class="mt-2 text-sm text-slate-500">{{ __('activities.empty_description') }}</p></div>
            @endforelse
        </div>
    </section>

    <div x-cloak x-show="isOpen" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="{{ __('activities.detail') }}">
        <button type="button" class="absolute inset-0 bg-slate-950/55" @click="close()" aria-label="{{ __('activities.close') }}"></button>
        <aside class="absolute inset-y-0 right-0 w-full max-w-3xl overflow-y-auto bg-slate-50 shadow-2xl">
            <header class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-200 bg-white p-5"><div><p class="crm-eyebrow">ACTIVITY · SITE</p><h2 class="mt-1 text-xl font-bold" x-text="detail?.opportunity?.site_name || @js(__('activities.loading'))"></h2></div><button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-xl" @click="close()" aria-label="{{ __('activities.close') }}">×</button></header>
            <div class="space-y-5 p-5">
                <p x-show="loading" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('activities.loading') }}</p>
                <p x-show="error" x-text="error" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800"></p>
                <template x-if="detail && !loading"><div class="space-y-5">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5"><h3 class="font-bold">{{ __('activities.next_action') }}</h3><p class="mt-3 text-sm text-slate-800" x-text="detail.sections.profile.next_action || @js(__('activities.unspecified'))"></p><p class="mt-2 text-xs text-slate-500" x-text="detail.sections.profile.next_action_at || @js(__('activities.no_date'))"></p></section>
                    <section class="rounded-2xl border border-slate-200 bg-white p-5"><div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-bold">{{ __('activities.history') }}</h3><p class="mt-1 text-sm text-slate-500">{{ __('activities.shared_pipeline') }}</p></div><button type="button" class="rounded-xl bg-cyan-700 px-4 py-2.5 text-sm font-semibold text-white" @click="openVisit()">+ {{ __('activities.add') }}</button></div><div class="mt-4 space-y-3"><template x-for="visit in detail.sections.visits" :key="`${visit.origin}-${visit.id}`"><article class="rounded-xl border border-slate-200 p-4"><div class="flex flex-wrap justify-between gap-2"><strong x-text="visit.summary"></strong><span class="text-xs text-slate-500" x-text="visit.occurred_at"></span></div><p class="mt-2 text-sm text-slate-600" x-text="visit.next_steps"></p></article></template><p x-show="detail.sections.visits.length === 0" class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">{{ __('activities.no_history') }}</p></div></section>
                    <form x-show="visitFormOpen" @submit.prevent="await saveVisit(); if (!visitError) window.location.reload()" class="rounded-2xl border border-cyan-200 bg-white p-5"><h3 class="font-bold">{{ __('activities.add_follow_up') }}</h3><p x-show="visitError" x-text="visitError" class="mt-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-700"></p><div class="mt-4 grid gap-4 sm:grid-cols-2"><label><span class="crm-field-label">{{ __('activities.type') }}</span><select x-ref="visitTypeInput" x-model="visitForm.visit_type" class="crm-site-input"><option value="FOLLOW_UP">{{ __('activities.follow_up') }}</option><option value="MEETING">{{ __('activities.meeting') }}</option><option value="SITE_VISIT">{{ __('activities.site_visit') }}</option><option value="SITE_SURVEY">{{ __('activities.survey') }}</option><option value="NEGOTIATION">{{ __('activities.negotiation') }}</option></select></label><label><span class="crm-field-label">{{ __('activities.datetime') }}</span><input type="datetime-local" x-model="visitForm.occurred_at" required class="crm-site-input"></label><label class="sm:col-span-2"><span class="crm-field-label">{{ __('activities.internal_participants') }}</span><input x-model="visitForm.internal_participants" required class="crm-site-input"></label><label class="sm:col-span-2"><span class="crm-field-label">{{ __('activities.details') }}</span><textarea x-model="visitForm.summary" required rows="3" class="crm-site-input"></textarea></label><label class="sm:col-span-2"><span class="crm-field-label">{{ __('activities.next_action') }}</span><textarea x-model="visitForm.next_steps" required rows="2" class="crm-site-input"></textarea></label><label><span class="crm-field-label">{{ __('activities.next_due') }}</span><input type="datetime-local" x-model="visitForm.next_action_at" class="crm-site-input"></label></div><div class="mt-4 flex gap-3"><button type="submit" :disabled="visitSaving" class="rounded-xl bg-cyan-700 px-5 py-2.5 text-sm font-semibold text-white" x-text="visitSaving ? @js(__('activities.saving')) : @js(__('activities.save'))"></button><button type="button" class="px-4 py-2.5 text-sm text-slate-600" @click="visitFormOpen=false">{{ __('activities.cancel') }}</button></div></form>
                </div></template>
            </div>
        </aside>
    </div>
</div>
@endsection

