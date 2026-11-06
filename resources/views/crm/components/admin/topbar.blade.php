<header class="crm-topbar" style="position: relative; z-index: 100;">
    <button class="crm-menu-toggle" type="button" data-nav-toggle aria-controls="admin-sidebar" aria-expanded="false">
        <span class="sr-only">{{ __('ui.toggle_navigation') }}</span><span aria-hidden="true">☰</span>
    </button>
    <div class="crm-topbar__context">
        <p>GSB EV CRM</p><span>{{ __('ui.workspace') }}</span>
    </div>
    <form method="GET" action="{{ route('crm.admin.search') }}" class="crm-global-search" role="search">
        <label class="sr-only" for="crm-global-search">{{ __('ui.search') }}</label>
        <input id="crm-global-search" name="q" type="search" maxlength="100" required
            placeholder="{{ __('ui.search_placeholder') }}"
            value="{{ request()->routeIs('crm.admin.search') ? request('q') : '' }}">
        <button type="submit">{{ __('ui.search') }}</button>
    </form>
    <div class="crm-account">
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open"
                class="relative inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg text-slate-700"
                aria-label="{{ __('ui.notifications') }}" :aria-expanded="open">
                <span aria-hidden="true">🔔</span>
                @if($unreadCount > 0)<span
                class="absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-600 px-1.5 py-0.5 text-center text-[10px] font-bold text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif
            </button>
            <div x-show="open" x-cloak
                class="absolute right-0 z-[100] mt-2 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                {{-- <div x-show="open" x-cloak
                    class="absolute right-0 z-40 mt-2 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    --}}
                    <header class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <strong>{{ __('ui.notifications') }}</strong><span
                            class="text-xs text-slate-500">{{ __('ui.unread', ['count' => $unreadCount]) }}</span>
                    </header>
                    <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                        @forelse($notifications as $item)
                        @php($localized = (json_decode($item->translations ?? 'null', true) ?: [])[app()->getLocale()] ?? null)
                        <form method="POST" action="{{ route('crm.admin.notifications.open', $item->id) }}"
                            class="{{ $item->read_at ? '' : 'bg-cyan-50/60' }}">@csrf<button
                                class="w-full p-4 text-left"><span
                                    class="block text-sm font-semibold text-slate-900">{{ $localized['title'] ?? $item->title }}</span><span
                                    class="mt-1 line-clamp-2 block text-xs text-slate-600">{{ $localized['body'] ?? $item->body }}</span></button>
                        </form>
                        @empty<p class="p-6 text-center text-sm text-slate-500">{{ __('ui.no_notifications') }}</p>
                        @endforelse
                    </div>
                    <a href="{{ route('crm.admin.notifications.index') }}"
                        class="block border-t border-slate-200 p-3 text-center text-sm font-semibold text-cyan-800">{{ __('ui.all_notifications') }}</a>
                </div>
            </div>
            <form method="POST" action="{{ route('crm.admin.locale.update') }}">@csrf<label class="sr-only"
                    for="crm-locale">{{ __('ui.language') }}</label><select id="crm-locale" name="locale"
                    onchange="this.form.submit()" class="rounded-xl border-slate-200 py-2 text-sm">
                    <option value="th" @selected(app()->getLocale() === 'th')>ไทย</option>
                    <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                    <option value="zh" @selected(app()->getLocale() === 'zh')>中文</option>
                </select></form>
            <span class="crm-account__avatar"
                aria-hidden="true">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
            <span class="crm-account__name">{{ auth()->user()->name ?? 'Administrator' }}</span>
            <span
                class="hidden rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-800 sm:inline-flex">{{ auth()->user()->role?->name ?? 'No role' }}</span>
            <form method="POST" action="{{ route('crm.logout') }}">@csrf<button class="crm-signout"
                    type="submit">{{ __('ui.sign_out') }}</button></form>
        </div>
</header>
