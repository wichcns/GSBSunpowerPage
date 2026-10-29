@extends('crm.layouts.admin')

@section('content')
<div class="space-y-6">
    <header class="crm-page-intro flex flex-wrap items-end justify-between gap-4">
        <div><p class="crm-eyebrow">NOTIFICATION CENTER</p><h1>{{ __('notifications.title') }}</h1><p>{{ __('notifications.description') }}</p></div>
        @if($unreadCount > 0)<form method="POST" action="{{ route('crm.admin.notifications.read-all') }}">@csrf<button class="rounded-xl border border-cyan-200 bg-white px-4 py-3 text-sm font-semibold text-cyan-800">{{ __('notifications.read_all') }}</button></form>@endif
    </header>
    @if(session('status'))<p class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex items-center justify-between border-b border-slate-200 p-5"><h2 class="font-bold">{{ __('notifications.history') }}</h2><span class="text-sm text-slate-500">{{ __('notifications.unread_count', ['count' => $unreadCount]) }}</span></header>
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $item)
                @php($localized = (json_decode($item->translations ?? 'null', true) ?: [])[app()->getLocale()] ?? null)
                <form method="POST" action="{{ route('crm.admin.notifications.open', $item->id) }}" class="{{ $item->read_at ? 'bg-white' : 'bg-cyan-50/60' }}">@csrf
                    <button class="grid w-full gap-2 p-5 text-left sm:grid-cols-[1fr_auto] sm:items-center">
                        <span><span class="font-semibold text-slate-950">{{ $localized['title'] ?? $item->title }}</span><span class="mt-1 block whitespace-pre-line text-sm text-slate-600">{{ $localized['body'] ?? $item->body }}</span></span>
                        <span class="text-xs text-slate-500">{{ $item->created_at }}{{ $item->read_at ? '' : ' · '.__('notifications.unread') }}</span>
                    </button>
                </form>
            @empty
                <div class="p-12 text-center"><p class="font-semibold text-slate-900">{{ __('notifications.empty') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('notifications.empty_description') }}</p></div>
            @endforelse
        </div>
        @if($notifications->hasPages())<footer class="border-t border-slate-200 p-5">{{ $notifications->links() }}</footer>@endif
    </section>
</div>
@endsection
