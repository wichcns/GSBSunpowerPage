@extends('crm.layouts.admin')

@section('content')
<div class="space-y-6">
    <header class="crm-page-intro">
        <p class="crm-eyebrow">{{ __('manuals.eyebrow') }}</p>
        <h1>{{ __('manuals.title') }}</h1>
        <p>{{ __('manuals.description') }}</p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2" aria-label="{{ __('manuals.choose_language') }}">
        @foreach (['th' => 'TH', 'zh' => 'CH'] as $code => $label)
            <a href="{{ route('crm.admin.manuals.index', ['language' => $code]) }}"
                @if($language === $code) aria-current="page" @endif
                class="rounded-2xl border p-5 shadow-sm transition hover:border-cyan-400 hover:bg-cyan-50 {{ $language === $code ? 'border-cyan-500 bg-cyan-50' : 'border-slate-200 bg-white' }}">
                <span class="text-xl font-bold text-slate-950">{{ $label }}</span>
                <span class="ml-3 text-sm text-slate-600">{{ __('manuals.language_'.$code) }}</span>
            </a>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="{{ __('manuals.preview') }}">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4 sm:px-6">
            <h2 class="font-semibold text-slate-950">{{ __('manuals.language_'.$language) }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('crm.admin.manuals.show', ['language' => $language]) }}" target="_blank" rel="noopener noreferrer"
                    class="rounded-xl border border-cyan-300 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-50">{{ __('manuals.open_new_tab') }} ↗</a>
                <a href="{{ route('crm.admin.manuals.show', ['language' => $language, 'download' => 1]) }}"
                    class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('manuals.download') }}</a>
            </div>
        </div>
        <iframe src="{{ route('crm.admin.manuals.show', ['language' => $language]) }}" title="{{ __('manuals.language_'.$language) }}"
            class="w-full" style="height: 70vh; min-height: 480px" loading="lazy"></iframe>
    </section>
</div>
@endsection
