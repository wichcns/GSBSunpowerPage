@php($directoryRoute = !empty($embedded) ? 'crm.admin.pipeline.index' : 'crm.admin.sites.index')
<div class="crm-sites"
        x-data="{ ...siteDirectory({{ Js::from(route('crm.admin.sites.index')) }}, {{ Js::from(route('crm.admin.sites.store')) }}, {{ Js::from(request('site')) }}, {{ Js::from(['loadFailed' => __('sites.load_failed')]) }}), deletedContactIds: [], removeContact(index) { const contact = this.contacts[index]; if (contact.id && !window.confirm(@js(__('sites.remove').'?'))) return; if (contact.id) this.deletedContactIds.push(contact.id); const wasPrimary = contact.is_primary; this.contacts.splice(index, 1); if (wasPrimary && this.contacts.length) this.setPrimary(0); } }">
        <section class="crm-page-intro flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="crm-eyebrow">CUSTOMER & SITE MASTER</p>
                <h1>{{ __('sites.title') }}</h1>
                <p>{{ __('sites.description') }}</p>
            </div>
            <button type="button" @click="deletedContactIds = []; openCreate()"
                class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-cyan-800">＋
                {{ __('sites.add') }}</button>
        </section>

        @if(session('status'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <strong>{{ __('sites.check') }}</strong>
                <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)
                <li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route($directoryRoute) }}"
                class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-5 sm:grid-cols-2 xl:grid-cols-6">
                @if(!empty($embedded))<input type="hidden" name="section" value="sites">@endif
                <label class="md:col-span-2"><span
                        class="mb-1 block text-xs font-semibold text-slate-600">{{ __('sites.search') }}</span><input name="q"
                        value="{{ request('q') }}" placeholder="{{ __('sites.search_placeholder') }}"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500"></label>
                <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('sites.area') }}</span><select name="area"
                        class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">{{ __('sites.all') }}</option>@foreach($areas as $area)
                        <option value="{{ $area }}" @selected(request('area') === $area)>{{ $area }}</option>@endforeach
                    </select></label>
                <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('sites.type') }}</span><select
                        name="location_type" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">{{ __('sites.all') }}</option>@foreach($types as $type)
                            <option value="{{ $type }}" @selected(request('location_type') === $type)>{{ \App\Crm\Support\LocationTypes::label($type) }}</option>
                        @endforeach
                    </select></label>
                <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('sites.owner') }}</span><select
                        name="owner_user_id" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">{{ __('sites.all') }}</option>@foreach($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((string) request('owner_user_id') === (string) $owner->id)>
                        {{ $owner->name }}</option>@endforeach
                    </select></label>
                <label><span class="mb-1 block text-xs font-semibold text-slate-600">{{ __('sites.completeness') }}</span><select
                        name="completeness" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">{{ __('sites.all') }}</option>
                        <option value="complete" @selected(request('completeness') === 'complete')>{{ __('sites.complete') }}</option>
                        <option value="incomplete" @selected(request('completeness') === 'incomplete')>{{ __('sites.incomplete') }}</option>
                    </select></label>
                <div class="flex items-end gap-2 xl:col-span-6"><button
                        class="rounded-xl bg-cyan-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('sites.search') }}</button><a
                        href="{{ route($directoryRoute, !empty($embedded) ? ['section' => 'sites'] : []) }}"
                        class="px-2 py-2.5 text-sm text-slate-500 hover:text-slate-900">{{ __('sites.clear') }}</a></div>
            </form>
            <div class="border-b border-slate-100 px-5 py-3 text-xs font-medium text-slate-500">{{ __('sites.found', ['count' => number_format($sites->total())]) }}</div>
            <div class="overflow-x-auto crm-mobile-table">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">{{ __('sites.site') }}</th>
                            <th class="px-5 py-3">{{ __('sites.area_type') }}</th>
                            <th class="px-5 py-3">{{ __('sites.primary_contact') }}</th>
                            <th class="px-5 py-3">{{ __('sites.owner') }}</th>
                            <th class="px-5 py-3"><span class="sr-only">{{ __('sites.details') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sites as $site)
                            <tr class="transition hover:bg-cyan-50/40">
                                <td class="px-5 py-4" data-label="{{ __('sites.site') }}"><button type="button" @click="deletedContactIds = []; openSite({{ $site->id }})"
                                        class="text-left font-semibold text-slate-950 hover:text-cyan-700">{{ $site->name }}</button>
                                    <div class="mt-1 text-xs text-slate-400">{{ $site->site_code }}</div>
                                    @if(filter_var($site->map_url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($site->map_url, PHP_URL_SCHEME) ?: ''), ['http', 'https'], true))
                                        <a href="{{ $site->map_url }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-xs font-semibold text-cyan-700">{{ __('location_types.open_map') }} ↗</a>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600" data-label="{{ __('sites.area_type') }}">{{ $site->area ?: '—' }}
                                    <div class="mt-1 text-xs text-slate-400">{{ \App\Crm\Support\LocationTypes::label($site->location_type) }}</div>
                                </td>
                                <td class="px-5 py-4 text-slate-600" data-label="{{ __('sites.primary_contact') }}">
                                    @php($contact = $site->contacts->firstWhere('is_primary', true) ?? $site->contacts->first())
                                    {{ $contact?->name ?? ($contactPreviews[$site->id]['name'] ?? __('sites.no_contact')) }}
                                    @if($contact?->phone || $contact?->line_name || $contact?->email)
                                        <div class="mt-1 text-xs text-slate-400">{{ $contact->phone ?: ($contact->line_name ? 'LINE: '.$contact->line_name : $contact->email) }}</div>
                                    @endif
                                    @if($site->contacts->isEmpty() && isset($contactPreviews[$site->id]))
                                        <div class="mt-1 text-xs text-slate-400">{{ $contactPreviews[$site->id]['source'] === 'current' ? __('sites.opportunity_source') : __('sites.legacy_source') }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600" data-label="{{ __('sites.owner') }}">{{ $site->owner?->name ?? $site->legacy_owner_label ?? __('sites.unassigned') }}@if(!$site->owner && $site->legacy_owner_label)<div class="mt-1 text-xs text-slate-400">{{ __('sites.legacy_source') }}</div>@endif</td>
                                <td class="px-5 py-4 text-right crm-mobile-table__action"><button type="button" @click="deletedContactIds = []; openSite({{ $site->id }})"
                                        aria-label="{{ __('sites.view', ['name' => $site->name]) }}"
                                        class="inline-grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-cyan-700 transition hover:border-cyan-300 hover:bg-cyan-50">↗</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="text-3xl">⌁</div>
                                    <p class="mt-3 font-semibold text-slate-800">{{ __('sites.empty') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('sites.empty_note') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sites->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $sites->links() }}</div>@endif
        </section>

        <div x-show="open" x-cloak @keydown.escape.window="close()" class="crm-overlay-layer fixed inset-0 z-50" role="dialog"
            aria-modal="true" aria-label="{{ __('sites.dialog') }}">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]" @click="close()"></div>
            <div class="absolute inset-y-0 right-0 flex w-full max-w-3xl flex-col bg-slate-50 shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-5 sm:px-8">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-cyan-700" x-text="site.site_code || 'NEW SITE'">
                        </p>
                        <h2 class="mt-1 text-xl font-bold text-slate-950" x-text="site.name || @js(__('sites.add'))"></h2>
                        <p class="mt-1 text-xs text-slate-500">{{ __('sites.site_contact') }}</p>
                        <p x-show="site.stage_label" class="mt-2 inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-800">
                            {{ __('sites.sales_stage') }}: <span class="ml-1" x-text="site.stage_label"></span>
                        </p>
                        <a x-show="site.pipeline_url" :href="site.pipeline_url"
                            class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-cyan-700 hover:text-cyan-900 hover:underline">{{ __('sites.view_opportunity') }} <span aria-hidden="true">↗</span></a>
                        @if(auth()->user()->hasPermission('sites.write') && auth()->user()->hasPermission('pipeline.view'))
                            <form x-show="site.id && site.opportunity_create_url" :action="site.opportunity_create_url" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('sites.create_opportunity') }}</button>
                            </form>
                        @endif
                    </div><button type="button" @click="close()" aria-label="{{ __('sites.close') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-slate-500 hover:bg-slate-100">✕</button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-6 sm:px-8">
                    <p x-show="loading" class="rounded-xl bg-white p-5 text-sm text-slate-500">{{ __('sites.loading') }}</p>
                    <p x-show="error" x-text="error" class="mb-4 rounded-xl bg-rose-50 p-4 text-sm text-rose-700"></p>
                    <form x-show="!loading" :action="formAction" method="POST" id="site-editor" class="space-y-5">
                        @csrf
                        <input type="hidden" name="_method" :value="site.id ? 'PATCH' : 'POST'">
                        <input x-show="site.id" type="hidden" name="site_fingerprint" :value="site.edit_fingerprint || ''" :disabled="!site.id">
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="mb-5 font-bold text-slate-900">{{ __('sites.site_info') }}</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="sm:col-span-2"><span class="crm-field-label">{{ __('sites.site_name') }} *</span><input
                                        required name="name" x-model="site.name" class="crm-site-input"></label>
                                <label><span class="crm-field-label">{{ __('sites.area') }}</span><input name="area" x-model="site.area"
                                        class="crm-site-input"></label>
                                <label><span class="crm-field-label">{{ __('sites.type') }}</span><select name="location_type" x-model="site.location_type" class="crm-site-input">
                                        <option value="">{{ __('sites.unspecified_type') }}</option>
                                        <template x-if="site.location_type && !@js(\App\Crm\Support\LocationTypes::codes()).includes(site.location_type)"><option :value="site.location_type" x-text="@js(\App\Crm\Support\LocationTypes::legacyLabels())[site.location_type] || site.location_type"></option></template>
                                        @foreach(\App\Crm\Support\LocationTypes::codes() as $code)<option value="{{ $code }}">{{ __('location_types.'.$code) }}</option>@endforeach
                                    </select></label>
                                <label><span class="crm-field-label">{{ __('sites.source') }}</span><input name="source"
                                        x-model="site.source" class="crm-site-input"></label>
                                <label><span class="crm-field-label">{{ __('sites.owner') }}</span><select name="owner_user_id"
                                        x-model="site.owner_user_id" class="crm-site-input">
                                        <option value="">{{ __('sites.unassigned') }}</option>@foreach($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach
                                    </select><span x-show="site.legacy_owner_label" class="mt-1 block text-xs text-slate-400">{{ __('sites.legacy') }}: <span x-text="site.legacy_owner_label"></span></span></label>
                                <label class="sm:col-span-2"><span class="crm-field-label">{{ __('sites.map') }}</span><input
                                        type="url" name="map_url" x-model="site.map_url" class="crm-site-input"
                                        placeholder="https://..."><a x-show="/^https?:\/\//i.test(site.map_url || '')" :href="site.map_url" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-sm font-semibold text-cyan-700">{{ __('location_types.open_map') }} ↗</a></label>
                                <label class="sm:col-span-2"><span class="crm-field-label">{{ __('sites.notes') }}</span><textarea
                                        name="notes" x-model="site.notes" rows="3"
                                        class="crm-site-input"></textarea></label>
                            </div>
                        </section>
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ __('sites.contacts') }}</h3>
                                    <p class="text-xs text-slate-500">{{ __('sites.contacts_note') }}</p>
                                </div><button type="button" @click="addContact()"
                                    class="rounded-lg bg-cyan-50 px-3 py-2 text-xs font-bold text-cyan-800 hover:bg-cyan-100">＋
                                    {{ __('sites.add_contact') }}</button>
                            </div>
                            <template x-for="(contact, index) in contacts" :key="contact.key">
                                <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4"><input type="hidden"
                                        :name="`contacts[${index}][id]`" :value="contact.id" :disabled="!contact.id">
                                    <div class="grid gap-3 sm:grid-cols-2"><label><span class="crm-field-label">{{ __('sites.name') }}
                                                *</span><input required :name="`contacts[${index}][name]`"
                                                x-model="contact.name" class="crm-site-input"></label><label><span
                                                class="crm-field-label">{{ __('sites.role') }}</span><input
                                                :name="`contacts[${index}][role]`" x-model="contact.role"
                                                class="crm-site-input"></label><label><span
                                                class="crm-field-label">{{ __('sites.phone') }}</span><input
                                                :name="`contacts[${index}][phone]`" x-model="contact.phone"
                                                class="crm-site-input"></label><label><span
                                                class="crm-field-label">LINE</span><input
                                                :name="`contacts[${index}][line_name]`" x-model="contact.line_name"
                                                class="crm-site-input"></label><label class="sm:col-span-2"><span
                                                class="crm-field-label">{{ __('sites.email') }}</span><input type="email"
                                                :name="`contacts[${index}][email]`" x-model="contact.email"
                                                class="crm-site-input"></label></div>
                                    <div class="mt-3 flex items-center justify-between"><label
                                            class="flex items-center gap-2 text-xs font-medium text-slate-600"><input
                                                type="radio" name="primary_contact" :checked="contact.is_primary"
                                                @change="setPrimary(index)"
                                                class="text-cyan-700">{{ __('sites.primary') }}</label><input type="hidden"
                                            :name="`contacts[${index}][is_primary]`"
                                            :value="contact.is_primary ? 1 : 0"><button type="button"
                                            @click="removeContact(index)" class="text-xs text-rose-600">{{ __('sites.remove') }}</button>
                                    </div>
                                </div>
                            </template>
                            <template x-for="contactId in deletedContactIds" :key="`deleted-${contactId}`"><input type="hidden"
                                    name="deleted_contact_ids[]" :value="contactId"></template>
                            <p x-show="contacts.length === 0 && site.contact_preview"
                                class="rounded-xl border border-cyan-200 bg-cyan-50 p-5 text-xs text-cyan-900">
                                {{ __('sites.preview', ['source' => '']) }} <span x-text="site.contact_preview?.source === 'current' ? @js(__('sites.opportunity_source')) : @js(__('sites.legacy'))"></span>
                                <strong x-text="site.contact_preview?.name"></strong> · {{ __('sites.not_added') }}
                            </p>
                            <p x-show="contacts.length === 0 && !site.contact_preview"
                                class="rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-500">
                                {{ __('sites.no_contact') }}</p>
                        </section>
                        <section x-show="site.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-bold text-slate-900">{{ __('sites.history') }}</h3><template
                                x-for="audit in site.audits || []" :key="audit.id">
                                <div class="mt-3 border-l-2 border-cyan-200 pl-4 text-xs text-slate-600"><strong
                                        x-text="audit.actor?.name || 'System'"></strong> · <span
                                        x-text="audit.action"></span>
                                    <p class="mt-1 text-slate-400" x-text="new Date(audit.created_at).toLocaleString()"></p>
                                </div>
                            </template>
                        </section>
                    </form>
                </div>
                <div class="border-t border-slate-200 bg-white px-5 py-4 sm:px-8"><button form="site-editor" type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-teal-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-900/10 hover:from-teal-700 hover:to-sky-700">{{ __('sites.save') }}</button>
                </div>
            </div>
        </div>
    </div>
