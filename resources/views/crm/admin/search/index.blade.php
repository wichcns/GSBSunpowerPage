@extends('crm.layouts.admin')

@section('content')
<div class="space-y-5">
    <header class="crm-page-intro">
        <p class="crm-eyebrow">{{ __('crm_headings.search') }}</p>
        <h1>{{ __('ui.search_results') }}</h1>
        <p>{{ __('ui.searching_for', ['query' => $query]) }}</p>
    </header>
    @foreach(['sites' => 'search_sites', 'pipeline' => 'search_pipeline', 'contracts' => 'search_contracts'] as $group => $label)
        @if(array_key_exists($group, $results))
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <h2 class="border-b border-slate-100 px-5 py-4 text-base font-bold text-slate-950">{{ __('ui.'.$label) }}</h2>
                @forelse($results[$group] as $row)
                    @php($isSite = $group === 'sites')
                    @php($title = $isSite ? $row->name : $row['site_name'])
                    @php($meta = $isSite ? implode(' · ', array_filter([$row->site_code, $row->area])) : implode(' · ', array_filter([$row['site_code'] ?? null, $row['area'] ?? null])))
                    @php($url = $isSite ? route('crm.admin.pipeline.index', ['site' => $row->id]) : ($group === 'pipeline' ? route('crm.admin.pipeline.index', ['open' => $row['source_id']]) : route('crm.admin.contracts.index', ['q' => $query])))
                    <a href="{{ $url }}" class="block border-b border-slate-100 px-5 py-4 transition hover:bg-cyan-50/50 last:border-0">
                        <strong class="block text-sm text-slate-950">{{ $title }}</strong>
                        @if($meta !== '')<span class="mt-1 block text-xs text-slate-500">{{ $meta }}</span>@endif
                        @if($isSite && ($contact = $row->contacts->firstWhere('is_primary', true) ?? $row->contacts->first()))<span class="mt-1 block text-xs text-slate-600">{{ $contact->name }}{{ $contact->phone ? ' · '.$contact->phone : '' }}</span>@endif
                        @if(!$isSite && $group === 'pipeline')<span class="mt-1 block text-xs text-slate-600">{{ $row['display_stage_label'] }}</span>@endif
                    </a>
                @empty
                    <p class="px-5 py-8 text-sm text-slate-500">{{ __('ui.search_empty') }}</p>
                @endforelse
            </section>
        @endif
    @endforeach
</div>
@endsection
