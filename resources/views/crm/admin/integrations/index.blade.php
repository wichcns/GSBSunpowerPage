@extends('crm.layouts.admin')

@section('content')
<div class="space-y-6">
    <header class="crm-page-intro"><p class="crm-eyebrow">SYSTEM · LINE OA</p><h1>{{ __('integrations.title') }}</h1><p>{{ __('integrations.description') }}</p></header>
    @if(session('status'))<p class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    @if(session('error'))<p class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</p>@endif
    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ __('integrations.token_secret') }}</p><strong class="mt-2 block {{ $lineReady ? 'text-emerald-700' : 'text-amber-700' }}">{{ $lineReady ? __('integrations.configured') : __('integrations.not_configured') }}</strong></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ __('integrations.environment_sending') }}</p><strong class="mt-2 block {{ $sendingEnabled ? 'text-emerald-700' : 'text-slate-700' }}">{{ $sendingEnabled ? __('integrations.enabled') : __('integrations.disabled') }}</strong></article>
        <article class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm"><p class="text-sm text-cyan-800">{{ __('integrations.delivery_scope') }}</p><strong class="mt-2 block text-cyan-950">{{ __('integrations.confirmed_group_only') }}</strong><p class="mt-2 text-xs text-cyan-800">{{ __('integrations.no_broadcast') }}</p></article>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold">{{ __('integrations.setup_steps') }}</h2>
        <ol class="mt-4 list-inside list-decimal space-y-2 text-sm text-slate-700"><li>{{ __('integrations.step_enable_api') }}</li><li>{!! __('integrations.step_webhook', ['url' => '<code>'.e(url('/crm/webhooks/line')).'</code>']) !!}</li><li>{{ __('integrations.step_invite') }}</li><li>{{ __('integrations.step_confirm') }}</li></ol>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header><h2 class="text-lg font-bold">{{ __('integrations.automatic_events') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('integrations.automatic_events_description') }}</p></header>
        <form method="POST" action="{{ route('crm.admin.integrations.line.settings') }}" class="mt-5">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($eventLabels as $event => $label)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-medium">
                        <input type="checkbox" name="events[]" value="{{ $event }}" @checked((bool) ($notificationSettings[$event] ?? false))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mt-5 flex justify-end"><button class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">{{ __('integrations.save_notifications') }}</button></div>
        </form>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 p-5"><h2 class="font-bold">{{ __('integrations.detected_groups') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('integrations.detected_groups_description') }}</p></header>
        <div class="divide-y divide-slate-100">@forelse($groups as $group)<form method="POST" action="{{ route('crm.admin.integrations.line.confirm') }}" class="flex flex-wrap items-center gap-4 p-5">@csrf<input type="hidden" name="group_id" value="{{ $group->group_id }}"><div class="min-w-0 flex-1"><strong class="break-all">{{ $group->display_name ?: $group->group_id }}</strong><p class="mt-1 text-xs text-slate-500">{{ __('integrations.last_seen', ['date' => $group->last_seen_at ?: '-']) }}</p></div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="enabled" value="1" @checked($group->is_enabled)> {{ __('integrations.enable_delivery') }}</label><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $group->is_confirmed ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $group->is_confirmed ? __('integrations.confirmed') : __('integrations.pending_confirmation') }}</span><button class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white">{{ __('integrations.confirm_group') }}</button></form>@empty<p class="p-10 text-center text-sm text-slate-500">{{ __('integrations.no_groups') }}</p>@endforelse</div>
        <footer class="flex justify-end border-t border-slate-200 p-5"><form method="POST" action="{{ route('crm.admin.integrations.line.test') }}">@csrf<button class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-3 text-sm font-semibold text-white">{{ __('integrations.send_test') }}</button></form></footer>
    </section>
    @php($statusLabels = __('integrations.statuses'))
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 p-5"><h2 class="font-bold">{{ __('integrations.recent_history') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('integrations.retry_description') }}</p></header>
        <div class="divide-y divide-slate-100">
            @forelse($outbox as $item)
                <div class="grid items-center gap-2 p-4 text-sm md:grid-cols-[10rem_1fr_10rem_8rem_12rem_auto]">
                    <strong>{{ $item->notification_type }}</strong>
                    <span class="truncate text-slate-600">{{ $item->recipient ?: __('integrations.no_recipient') }}</span>
                    <span class="font-medium {{ $item->status === 'sent' ? 'text-emerald-700' : (in_array($item->status, ['failed', 'exhausted']) ? 'text-rose-700' : 'text-amber-700') }}">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                    <span class="text-slate-600">{{ __('integrations.attempt', ['current' => $item->attempts, 'total' => 3]) }}</span>
                    <span class="text-slate-500">{{ $item->next_attempt_at ? __('integrations.next_attempt', ['date' => $item->next_attempt_at]) : $item->created_at }}</span>
                    @if(in_array($item->status, ['failed', 'exhausted']))
                        <form method="POST" action="{{ route('crm.admin.integrations.line.outbox.retry', $item->id) }}">@csrf<button class="rounded-xl border border-cyan-300 px-3 py-2 font-semibold text-cyan-800 hover:bg-cyan-50">{{ __('integrations.retry') }}</button></form>
                    @endif
                    @if($item->last_error)<p class="text-rose-700 md:col-span-6">{{ $item->last_error }}</p>@endif
                </div>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">{{ __('integrations.no_history') }}</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
