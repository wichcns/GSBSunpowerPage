@extends('crm.layouts.admin')

@section('content')
<div class="crm-ownership space-y-6">
    <header class="crm-page-intro flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="crm-eyebrow">{{ __('crm_headings.ownership') }}</p>
            <h1>{{ __('ownership.title') }}</h1>
            <p>{{ __('ownership.description') }}</p>
        </div>
        <a href="{{ route('crm.admin.my-work') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-cyan-800 hover:bg-cyan-50">{{ __('ownership.my_work') }}</a>
    </header>

    @if(session('status'))
        <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong>{{ __('ownership.cannot_save') }}</strong>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <p class="rounded-2xl border border-cyan-100 bg-cyan-50 p-5 text-sm leading-6 text-cyan-950">
            {{ __('ownership.notice') }}

        </p>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-600">{{ __('ownership.unassigned') }}</p>
            <p class="mt-1 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($totalUnassigned) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('ownership.staff_accounts', ['count' => number_format($users->count())]) }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="{{ __('ownership.review') }}">
        <form method="GET" action="{{ route('crm.admin.ownership.index') }}" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 p-5">
            <label class="min-w-52 flex-1"><span class="crm-field-label">{{ __('ownership.search_label') }}</span>
                <input name="q" value="{{ request('q') }}" class="crm-site-input" placeholder="{{ __('ownership.search') }}"></label>
            <label class="min-w-48"><span class="crm-field-label">{{ __('ownership.legacy_owner') }}</span>
                <select name="label" class="crm-site-input"><option value="">{{ __('ownership.all') }}</option>
                    @foreach($labels as $label)
                        <option value="{{ $label->legacy_owner_label }}" @selected(request('label') === $label->legacy_owner_label)>{{ $label->legacy_owner_label }} ({{ $label->total }})</option>
                    @endforeach
                </select></label>
            <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('ownership.search') }}</button>
            <a href="{{ route('crm.admin.ownership.index') }}" class="px-2 py-2.5 text-sm text-slate-600">{{ __('ownership.clear') }}</a>
        </form>

        <form method="POST" action="{{ route('crm.admin.ownership.assign') }}">
            @csrf
            @foreach($sites as $site)
                <input type="hidden" name="fingerprints[{{ $site->id }}]" value="{{ $fingerprints[$site->id] }}">
            @endforeach
            <div class="flex flex-wrap items-end gap-3 border-b border-slate-200 p-5">
                <label class="min-w-56 flex-1"><span class="crm-field-label">{{ __('ownership.assign_selected') }}</span>
                    <select name="owner_user_id" required class="crm-site-input">
                        <option value="">{{ __('ownership.select_staff') }}</option>
                        @foreach($users as $user)<option value="{{ $user->id }}" @selected(old('owner_user_id') == $user->id)>{{ $user->name }} · {{ $user->email }}</option>@endforeach
                    </select></label>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:from-teal-700 hover:to-cyan-700">{{ __('ownership.confirm') }}</button>
            </div>
            <div class="overflow-x-auto crm-mobile-table">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-600"><tr>
                        <th class="w-14 px-5 py-3">{{ __('ownership.select') }}</th><th class="px-5 py-3">{{ __('ownership.site') }}</th><th class="px-5 py-3">{{ __('ownership.legacy_name') }}</th><th class="px-5 py-3">{{ __('ownership.account_hint') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sites as $site)
                            <tr class="hover:bg-cyan-50/40">
                                <td class="px-5 py-4" data-label="{{ __('ownership.select') }}"><input type="checkbox" name="site_ids[]" value="{{ $site->id }}" @checked(in_array($site->id, old('site_ids', []))) aria-label="{{ __('ownership.select_site', ['name' => $site->name]) }}" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600"></td>
                                <td class="px-5 py-4" data-label="{{ __('ownership.site') }}"><strong class="text-slate-950">{{ $site->name }}</strong><div class="mt-1 text-xs text-slate-500">{{ $site->site_code ?: __('ownership.no_code') }}</div></td>
                                <td class="px-5 py-4 text-slate-700" data-label="{{ __('ownership.legacy_name') }}">{{ $site->legacy_owner_label ?: __('ownership.no_legacy_name') }}</td>
                                <td class="px-5 py-4 text-slate-600" data-label="{{ __('ownership.account_hint') }}">
                                    @if(count($candidateNames[$site->id]) === 1)
                                        {{ __('ownership.single_match', ['name' => $candidateNames[$site->id][0]]) }}
                                    @elseif(count($candidateNames[$site->id]) > 1)
                                        {{ __('ownership.multiple_matches') }}
                                    @else
                                        {{ __('ownership.no_match') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-slate-500">{{ __('ownership.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        @if($sites->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $sites->links() }}</div>@endif
    </section>
</div>
@endsection

