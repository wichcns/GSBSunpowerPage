@extends('crm.layouts.admin')

@section('content')
<div class="crm-dashboard space-y-6">
    @php
        $scope = $overview['scope'];
        $title = match ($scope) {
            'personal' => __('dashboard.personal_title'),
            'staff_all' => __('dashboard.staff_title'),
            default => __('dashboard.organization_title'),
        };
        $description = match ($scope) {
            'personal' => __('dashboard.personal_description'),
            'staff_all' => __('dashboard.staff_description'),
            default => __('dashboard.organization_description'),
        };
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div class="crm-page-intro mb-0">
            <p class="crm-eyebrow">GSB EV · OVERVIEW</p>
            <h1>{{ __('dashboard.page_title') }}</h1>
            <p>{{ $description }}</p>
        </div>
        @if($overview['primary_url'])
            <a href="{{ $overview['primary_url'] }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white no-underline shadow-sm transition hover:bg-cyan-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700">
                {{ $scope === 'personal' ? __('dashboard.view_personal') : ($scope === 'staff_all' ? __('dashboard.view_team') : __('dashboard.view_pipeline')) }}
                <span aria-hidden="true" class="ml-2">↗</span>
            </a>
        @endif
    </header>

    <section aria-label="{{ __('dashboard.summary') }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($overview['cards'] as $key => $card)
            @continue(!$card['url'])
            <a href="{{ $card['url'] }}" data-card="{{ $key }}" class="crm-summary-card group flex min-h-36 flex-col justify-between rounded-2xl border p-5 no-underline shadow-sm transition hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700">
                <div>
                    <span class="crm-summary-card__accent mb-4 block h-1 w-12 rounded-full" aria-hidden="true"></span>
                    <p class="crm-summary-card__label text-sm font-semibold">{{ $card['label'] }}</p>
                </div>
                <div class="flex items-end justify-between gap-3">
                    <p class="crm-summary-card__value text-3xl font-bold tabular-nums">{{ number_format($card['value']) }}</p>
                    <span class="crm-summary-card__arrow grid h-8 w-8 place-items-center rounded-full text-lg transition group-hover:translate-x-0.5" aria-hidden="true">↗</span>
                </div>
            </a>
        @endforeach
    </section>

    @if($overview['charts'] !== [])
        <section aria-label="{{ __('dashboard.charts') }}" class="grid gap-5 xl:grid-cols-2">
            @foreach($overview['charts'] as $kind => $chart)
                @php($isSales = $kind === 'sales')
                <article aria-label="{{ $isSales ? __('dashboard.sales_progress') : __('dashboard.engineering_progress') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-cyan-700">{{ $isSales ? 'SALES PROGRESS' : 'ENGINEERING PROGRESS' }}</p>
                            <h2 class="mt-2 text-lg font-bold text-slate-950">{{ $isSales ? __('dashboard.sales_success') : __('dashboard.engineering_progress') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $isSales ? __('dashboard.sales_description') : __('dashboard.engineering_description') }}</p>
                        </div>
                        <div class="rounded-2xl bg-cyan-50 px-4 py-3 text-right">
                            <p class="text-3xl font-bold tabular-nums text-cyan-900">{{ $chart['percent'] }}%</p>
                            <p class="text-xs font-semibold text-cyan-800">{{ $chart['success_label'] }} {{ __('dashboard.from_total', ['success' => $chart['success'], 'total' => $chart['total']]) }}</p>
                        </div>
                    </div>

                    @if($chart['total'] > 0)
                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ __('dashboard.progress_aria', ['label' => $chart['success_label']]) }}" aria-valuenow="{{ $chart['success'] }}" aria-valuemin="0" aria-valuemax="{{ $chart['total'] }}">
                            <div class="h-full rounded-full bg-cyan-600" style="width: {{ $chart['percent'] }}%"></div>
                        </div>
                    @endif

                    @if($chart['total'] === 0)
                        <p class="mt-6 rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">{{ __('dashboard.no_chart') }}</p>
                    @else
                        <div class="mt-5 space-y-1">
                            @foreach($chart['stages'] as $stage)
                                @continue($stage['count'] === 0)
                                <a href="{{ $stage['url'] }}" class="group block rounded-xl px-2 py-2 text-slate-700 no-underline transition hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-700">
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="min-w-0 font-medium group-hover:text-cyan-800">{{ $stage['label'] }}</span>
                                        <span class="shrink-0 font-bold tabular-nums text-slate-950">{{ $stage['count'] }}</span>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $isSales ? 'bg-cyan-500' : 'bg-emerald-500' }}" style="width: {{ $stage['percent'] }}%"></div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        @if($scope === 'staff_all')
                            <p class="mt-4 text-xs text-slate-500">{{ __('dashboard.staff_chart_note') }}</p>
                        @endif
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(18rem,1fr)]">
        <section aria-label="{{ __('dashboard.attention') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-950">{{ $title }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $scope === 'personal' ? __('dashboard.personal_attention') : ($scope === 'staff_all' ? __('dashboard.staff_attention') : __('dashboard.organization_attention')) }}</p>
            </div>
            @forelse($overview['attention'] as $item)
                <a href="{{ $item['url'] }}" class="flex min-h-20 items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 text-slate-900 no-underline transition last:border-0 hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-700">
                    <div class="min-w-0">
                        <p class="break-words text-sm font-semibold">{{ $item['title'] }}</p>
                        <p class="mt-1 break-words text-xs text-slate-600">{{ $item['site_name'] }} · {{ $item['detail'] }}</p>
                    </div>
                    <span class="shrink-0 font-semibold text-cyan-800" aria-hidden="true">↗</span>
                </a>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="font-semibold text-slate-900">{{ __('dashboard.no_attention') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('dashboard.no_attention_description') }}</p>
                </div>
            @endforelse
        </section>

        <div class="space-y-5">
            <section aria-label="{{ __('dashboard.data_source') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-bold text-slate-950">{{ __('dashboard.data_source') }}</h2>
                @if($scope === 'staff_all')
                    <p class="mt-2 text-sm text-slate-600">{{ __('dashboard.staff_source') }}</p>
                @elseif($scope === 'personal')
                    <p class="mt-2 text-sm text-slate-600">{{ __('dashboard.personal_source') }}</p>
                @else
                    <p class="mt-2 text-sm text-slate-600">{{ __('dashboard.organization_source') }}</p>
                @endif
                @if($overview['updated_at'])
                    <p class="mt-4 text-xs text-slate-500">{{ __('dashboard.imported_at', ['date' => date('d/m/Y', strtotime($overview['updated_at']))]) }}</p>
                @else
                    <p class="mt-4 text-xs text-slate-500">{{ __('dashboard.no_import') }}</p>
                @endif
            </section>

            @if($scope === 'organization')
                <section aria-label="{{ __('dashboard.shortcuts') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-950">{{ __('dashboard.shortcuts') }}</h2>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                        @foreach([['pipeline.view', __('nav.pipeline'), 'crm.admin.pipeline.index'], ['contracts.view', __('nav.contracts'), 'crm.admin.contracts.index'], ['engineering.view', __('nav.engineering'), 'crm.admin.engineering.index'], ['team.view', __('nav.team_work'), 'crm.admin.team-work']] as [$permission, $label, $route])
                            @if(auth()->user()->hasPermission($permission))
                                <a href="{{ route($route) }}" class="flex min-h-11 items-center justify-between rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 no-underline hover:border-cyan-300 hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-700">{{ $label }} <span aria-hidden="true">↗</span></a>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    @if(!$overview['updated_at'] && array_sum(array_column($overview['cards'], 'value')) === 0)
        <section class="crm-welcome-card" aria-label="{{ __('dashboard.crm_data_status') }}">
            <span class="crm-welcome-card__spark" aria-hidden="true">ϟ</span>
            <div class="min-w-0">
                <h2>{{ __('dashboard.no_data') }}</h2>
                <p>{{ __('dashboard.no_data_description') }}</p>
                @if(auth()->user()->hasPermission('sites.view'))
                    <a href="{{ route('crm.admin.pipeline.index') }}" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white no-underline hover:bg-cyan-700">{{ __('dashboard.start_sites') }}</a>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection
