@extends('crm.layouts.admin')

@section('content')
<div class="crm-contracts space-y-5" x-data="contractsDirectory(@js(route('crm.admin.contracts.index')), @js(route('crm.admin.pipeline.index')), @js(['fields' => __('contracts.fields'), 'unspecified' => __('contracts.unspecified'), 'discard' => __('contracts.js.discard'), 'loadFailed' => __('contracts.js.load_failed'), 'conflict' => __('contracts.js.conflict'), 'saveFailed' => __('contracts.js.save_failed')]))" @keydown.escape.window="close()">
    <header class="crm-page-intro">
        <p class="crm-eyebrow">{{ __('crm_headings.contracts') }}</p>
        <h1>{{ __('contracts.title') }}</h1>
        <p>{{ __('contracts.description') }}</p>
    </header>
    @if(session('status'))<p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    @if($errors->any())<p role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ $errors->first() }}</p>@endif
    <section aria-label="{{ __('contracts.summary') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['total', 'total'], ['expired', 'expired'], ['next_90', 'next_90'], ['missing', 'missing']] as [$metric, $key])
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-600">{{ __('contracts.metrics.'.$metric) }}</p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-slate-950">{{ $summary[$key] }}</p>
        </article>
        @endforeach
    </section>
    <div class="flex flex-wrap gap-2 text-sm text-slate-700" aria-label="{{ __('contracts.attention') }}">
        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5">{{ __('contracts.missing_information', ['count' => $summary['missing_information']]) }}</span>
        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5">{{ __('contracts.documents_not_found', ['count' => $summary['document_not_found']]) }}</span>
    </div>
    <p class="text-xs text-slate-500">{{ __('contracts.file_note') }}</p>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form action="{{ route('crm.admin.contracts.index') }}" method="GET" class="grid gap-3 border-b border-slate-200 bg-slate-50 p-5 sm:grid-cols-2 xl:grid-cols-6">
            <label class="xl:col-span-2"><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('contracts.search') }}</span><input name="q" maxlength="100" value="{{ $filters['search'] }}" placeholder="{{ __('contracts.search_placeholder') }}" class="w-full rounded-xl border-slate-200 text-sm"></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('contracts.sales_stage') }}</span><select name="stage" class="w-full rounded-xl border-slate-200 text-sm"><option value="">{{ __('contracts.all') }}</option>@foreach($stages as $code => $label)<option value="{{ $code }}" @selected($filters['stage'] === $code)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('contracts.status') }}</span><select name="contract_status" class="w-full rounded-xl border-slate-200 text-sm"><option value="">{{ __('contracts.all') }}</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('contracts.expiry') }}</span><select name="expiry" class="w-full rounded-xl border-slate-200 text-sm"><option value="">{{ __('contracts.all') }}</option><option value="expired" @selected($filters['expiry'] === 'expired')>{{ __('contracts.metrics.expired') }}</option><option value="next_90" @selected($filters['expiry'] === 'next_90')>{{ __('contracts.within_90') }}</option><option value="missing" @selected($filters['expiry'] === 'missing')>{{ __('contracts.not_set') }}</option><option value="invalid" @selected($filters['expiry'] === 'invalid')>{{ __('contracts.check_date') }}</option></select></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('contracts.information') }}</span><select name="information" class="w-full rounded-xl border-slate-200 text-sm"><option value="">{{ __('contracts.all') }}</option><option value="missing" @selected($filters['information'] === 'missing')>{{ __('contracts.incomplete') }}</option></select></label>
            <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('contracts.document') }}</span><select name="document" class="w-full rounded-xl border-slate-200 text-sm"><option value="">{{ __('contracts.all') }}</option><option value="current" @selected($filters['document'] === 'current')>{{ __('contracts.current_file') }}</option><option value="legacy_metadata" @selected($filters['document'] === 'legacy_metadata')>{{ __('contracts.legacy_file') }}</option><option value="not_found" @selected($filters['document'] === 'not_found')>{{ __('contracts.no_file') }}</option></select></label>
            <div class="flex items-center gap-3 xl:col-span-6"><button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white">{{ __('contracts.search') }}</button><a href="{{ route('crm.admin.contracts.index') }}" class="text-sm text-slate-600">{{ __('contracts.clear') }}</a></div>
        </form>
        <div class="border-b border-slate-100 px-5 py-3 text-xs text-slate-500">{{ __('contracts.found', ['count' => $rows->total()]) }}</div>
        @forelse($rows as $row)
        <article class="border-b border-slate-100 p-5 last:border-0">
            <div class="grid gap-3 lg:grid-cols-[minmax(0,2fr)_repeat(6,minmax(0,1fr))_auto] lg:items-center">
            <div class="min-w-0">
                <h2 class="truncate font-semibold text-slate-950">{{ $row['site_name'] }}</h2>
                <p class="text-xs text-slate-500">{{ $row['site_code'] ?: $row['source_id'] }} · {{ $row['area'] ?: __('contracts.area_unspecified') }}</p>
                @if($row['missing_contract_details'])
                    <p class="mt-1 text-xs font-medium text-amber-700">{{ __('contracts.no_details') }}</p>
                @endif
                @if($row['missing_fields'] !== [])
                    <p class="mt-1 text-xs text-amber-700">{{ __('contracts.missing_fields', ['fields' => implode(', ', array_map(fn ($field) => __('contracts.fields.'.$field), $row['missing_fields']))]) }}</p>
                @endif
            </div>
            <div><p class="text-xs text-slate-500">{{ __('contracts.sales_stage') }}</p><p class="text-sm">{{ $row['display_stage_label'] }}</p></div>
            <div><p class="text-xs text-slate-500">{{ __('contracts.status') }}</p><p class="text-sm">{{ filled($row['contract_status']) ? $row['contract_status'] : __('contracts.unspecified') }}</p></div>
            <div><p class="text-xs text-slate-500">{{ __('contracts.term') }}</p><p class="text-sm">{{ filled($row['contract_term']) ? $row['contract_term'] : __('contracts.unspecified') }}</p></div>
            <div><p class="text-xs text-slate-500">{{ __('contracts.expiry') }}</p><p class="text-sm">{{ $row['expiry_class'] === 'invalid' ? __('contracts.check_date') : ($row['contract_expiry'] ?: __('contracts.unspecified')) }}</p></div>
            <div><p class="text-xs text-slate-500">{{ __('contracts.owner') }}</p><p class="text-sm">{{ $row['owner'] ?: __('contracts.unassigned') }}</p></div>
            <div><p class="text-xs text-slate-500">{{ __('contracts.document') }}</p><p class="text-sm">{{ match($row['document_status']) { 'current' => __('contracts.current_file'), 'legacy_metadata' => __('contracts.legacy_file'), default => __('contracts.no_file') } }}</p></div>
            <div class="flex flex-wrap gap-2"><button type="button" class="rounded-xl border border-cyan-200 px-3 py-2 text-center text-sm font-semibold text-cyan-800 hover:bg-cyan-50" @click="open(@js($row['source_id']), $event.currentTarget, 'details')" aria-label="{{ __('contracts.view_details', ['name' => $row['site_name']]) }}">{{ __('contracts.view') }}</button><button type="button" class="rounded-xl border border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="open(@js($row['source_id']), $event.currentTarget, 'files')" aria-label="{{ __('contracts.view_files', ['name' => $row['site_name']]) }}">{{ __('contracts.files') }}</button></div>
            </div>
            <div class="mt-4 grid gap-2 border-t border-slate-100 pt-4 text-xs text-slate-700 sm:grid-cols-2 xl:grid-cols-4">
                @foreach(['parking_spaces' => 'parking', 'commercial_model' => 'model', 'contract_signed_at' => 'signed', 'contract_effective_at' => 'effective', 'rent_free_months' => 'free', 'monthly_payment_day' => 'payment'] as $field => $label)
                    <p><span class="text-slate-500">{{ __('contract_terms.'.$label) }}:</span> {{ $row[$field] === null || $row[$field] === '' ? __('contract_terms.unspecified') : ($field === 'commercial_model' ? __('business_summary.'.$row[$field]) : $row[$field]) }}</p>
                @endforeach
            </div>
            @if(auth()->user()->hasPermission('pipeline.business'))
            <details class="mt-4 w-full max-w-2xl rounded-xl border border-slate-200 bg-slate-50 p-4">
                <summary class="cursor-pointer text-sm font-semibold text-cyan-800">{{ __('contract_terms.edit') }}</summary>
                <form method="POST" action="{{ route('crm.admin.contracts.terms.update', $row['source_id']) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                    @csrf @method('PATCH')
                    <input type="hidden" name="version" value="{{ $row['business_version'] }}">
                    <label class="text-sm">{{ __('contract_terms.parking') }}<input name="parking_spaces" type="number" min="0" value="{{ $row['parking_spaces'] }}" class="mt-1 block w-full rounded-xl border-slate-200"></label>
                    <label class="text-sm">{{ __('contract_terms.model') }}<select name="commercial_model" class="mt-1 block w-full rounded-xl border-slate-200"><option value="">{{ __('contract_terms.unspecified') }}</option>@foreach(['PARKING_RENT', 'AREA_RENT', 'REVENUE_SHARE', 'RENT', 'RENT_AND_SHARE'] as $model)<option value="{{ $model }}" @selected($row['commercial_model'] === $model)>{{ __('business_summary.'.$model) }}</option>@endforeach</select></label>
                    <label class="text-sm">{{ __('contract_terms.term') }}<input name="contract_term" value="{{ $row['contract_term'] }}" class="mt-1 block w-full rounded-xl border-slate-200"></label>
                    <label class="text-sm">{{ __('contract_terms.signed') }}<input name="contract_signed_at" type="date" value="{{ $row['contract_signed_at'] }}" class="mt-1 block w-full rounded-xl border-slate-200"></label>
                    <label class="text-sm">{{ __('contract_terms.effective') }}<input name="contract_effective_at" type="date" value="{{ $row['contract_effective_at'] }}" class="mt-1 block w-full rounded-xl border-slate-200"></label>
                    <label class="text-sm">{{ __('contract_terms.free') }}<input name="rent_free_months" type="number" min="0" value="{{ $row['rent_free_months'] }}" class="mt-1 block w-full rounded-xl border-slate-200"></label>
                    <label class="text-sm">{{ __('contract_terms.payment') }}<input name="monthly_payment_day" type="number" min="1" max="31" value="{{ $row['monthly_payment_day'] }}" class="mt-1 block w-full rounded-xl border-slate-200"></label>
                    <button class="self-end rounded-xl bg-cyan-700 px-4 py-2.5 text-sm font-semibold text-white">{{ __('contract_terms.save') }}</button>
                </form>
            </details>
            @endif
        </article>
        @empty
        <p class="p-10 text-center text-sm text-slate-500">{{ __('contracts.empty') }}</p>
        @endforelse
        <div class="p-5">{{ $rows->links() }}</div>
    </section>
    @include('crm.admin.contracts.partials.detail-drawer')
</div>
@endsection

