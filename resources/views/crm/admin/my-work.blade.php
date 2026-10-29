@extends('crm.layouts.admin')

@section('content')
    <div class="crm-my-work space-y-6"
        x-data="myWorkDetails(@js(route('crm.admin.pipeline.index')), @js(route('crm.admin.my-work.items')), @js($items), @js($summary), @js(array_intersect_key(__('my_work'), array_flip(['file_categories', 'file_groups', 'unknown_size', 'unspecified', 'load_failed', 'save_failed', 'refresh_failed', 'upload_failed', 'task_types', 'visit_types'])) + ['sales_stages' => __('stages.sales')]))"
        @keydown.escape.window="close()">
        <header class="crm-page-intro">
            <p class="crm-eyebrow">MY WORK</p>
            <h1>{{ __('my_work.title') }}</h1>
            <p>{{ __('my_work.description') }}</p>
        </header>

        <section aria-label="{{ __('my_work.summary') }}" class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-600">{{ __('my_work.total') }}</p>
                <p class="mt-1 text-3xl font-bold tabular-nums text-slate-950" x-text="summary.total"></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-600">{{ __('my_work.overdue') }}</p>
                <p class="mt-1 text-3xl font-bold tabular-nums text-rose-700" x-text="summary.overdue"></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-600">{{ __('my_work.undated') }}</p>
                <p class="mt-1 text-3xl font-bold tabular-nums text-amber-700" x-text="summary.undated"></p>
            </div>
        </section>

        <p class="rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">
            {{ __('my_work.scope') }}
            @if(auth()->user()->hasPermission('ownership.manage'))
                <a href="{{ route('crm.admin.ownership.index') }}"
                    class="ml-1 font-semibold underline">{{ __('my_work.verify_owner') }}</a>
            @endif
        </p>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            aria-label="{{ __('my_work.assigned_sites') }}">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-950">{{ __('my_work.assigned_sites') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('my_work.assigned_note') }}
                    </p>
                </div>
                <span class="text-sm text-slate-500">{{ __('my_work.site_count', ['count' => count($sites)]) }}</span>
            </div>
            @forelse($sites as $site)
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-0">
                    <div>
                        <p class="font-semibold text-slate-950">{{ $site['name'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $site['site_code'] ?: __('my_work.no_site_code') }}{{ $site['area'] ? ' · ' . $site['area'] : '' }}</p>
                    </div>
                    @if($site['source_id'])<button type="button" @click="openSite(@js($site['source_id']))"
                        class="min-h-11 text-sm font-semibold text-cyan-800 hover:underline">{{ __('my_work.view_site') }}
                    ↗</button>@endif
                </div>
            @empty
                <p class="p-5 text-sm text-slate-500">{{ __('my_work.no_sites') }}</p>
            @endforelse
        </section>
        <div x-show="isOpen" x-cloak class="crm-overlay-layer fixed inset-0 z-50 flex justify-end"
            @keydown.escape.window="close()">
            <button type="button" class="absolute inset-0 bg-slate-950/60" @click="close()"
                aria-label="{{ __('my_work.close_details') }}"></button>
            <section role="dialog" aria-modal="true" aria-labelledby="my-work-detail-title"
                class="relative flex h-full w-full max-w-2xl flex-col bg-slate-50 shadow-2xl">
                <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-5">
                    <div>
                        <p class="crm-eyebrow">MY WORK · SITE</p>
                        <h2 id="my-work-detail-title" class="mt-1 text-xl font-bold text-slate-950"
                            x-text="detail?.opportunity?.site_name || @js(__('my_work.site_details'))"></h2>
                    </div>
                    <button type="button" @click="close()" class="min-h-11 min-w-11 rounded-xl border border-slate-200"
                        aria-label="{{ __('my_work.close_details') }}">✕</button>
                </header>
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                    <p x-show="loading" role="status" class="text-sm text-slate-500">{{ __('my_work.loading') }}</p>
                    <p x-show="error" role="alert" class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800" x-text="error">
                    </p>
                    <template x-if="detail">
                        <div class="space-y-5">
                            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                                <h3 class="font-bold text-slate-900">{{ __('my_work.site_info') }}</h3>
                                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.area') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(detail.sections.profile.area)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.site_type') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(detail.sections.profile.location_type)">
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.sales_stage') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(translations.sales_stages?.[detail.opportunity.stage_code] ?? detail.opportunity.stage_label)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.owner') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(detail.sections.profile.assigned_to)"></dd>
                                    </div>
                                </dl>
                            </section>
                            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                                <h3 class="font-bold text-slate-900">{{ __('my_work.contact_next') }}</h3>
                                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.contact_name') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(detail.sections.profile.contact_name)">
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.phone') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(detail.sections.profile.phone)"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs text-slate-500">{{ __('my_work.next_action') }}</dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-sm"
                                            x-text="display(detail.sections.profile.next_action)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('my_work.due_date') }}</dt>
                                        <dd class="mt-1 text-sm" x-text="display(detail.sections.profile.next_action_at)">
                                        </dd>
                                    </div>
                                </dl>
                            </section>
                            <section class="rounded-2xl border border-slate-200 bg-white p-5" aria-label="{{ __('my_work.visits') }}">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-slate-900">{{ __('my_work.visits') }}</h3>
                                        <p class="mt-1 text-xs text-slate-500">{{ __('my_work.visits_note') }}</p>
                                    </div>
                                    <button type="button" @click="openVisit()"
                                        class="min-h-11 rounded-xl bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">+
                                        {{ __('my_work.add_record') }}</button>
                                </div>
                                <template x-if="(detail.sections.visits ?? []).length === 0">
                                    <p
                                        class="mt-4 rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">
                                        {{ __('my_work.no_visits') }}</p>
                                </template>
                                <div class="mt-4 space-y-3">
                                    <template x-for="visit in (detail.sections.visits ?? [])"
                                        :key="`${visit.origin}-${visit.id}`">
                                        <article class="rounded-xl border border-slate-200 p-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-semibold text-cyan-800"
                                                        x-text="visitTypeLabel(visit.visit_type)"></p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900"
                                                        x-text="display(visit.summary)"></p>
                                                    <p class="mt-1 text-xs text-slate-500"
                                                        x-text="display(visit.occurred_at)"></p>
                                                </div>
                                                <button x-show="visit.editable" type="button" @click="openVisit(visit)"
                                                    class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-cyan-800">{{ __('my_work.edit') }}</button>
                                            </div>
                                            <p class="mt-3 whitespace-pre-wrap text-sm text-slate-700"><span
                                                    class="font-semibold">{{ __('my_work.next_action_label') }} </span><span
                                                    x-text="display(visit.next_steps)"></span></p>
                                        </article>
                                    </template>
                                </div>
                                <form x-show="visitFormOpen" x-cloak @submit.prevent="saveVisit()"
                                    class="mt-5 space-y-4 rounded-2xl border border-cyan-200 bg-cyan-50/50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="font-bold text-slate-900"
                                            x-text="editingVisitId ? @js(__('my_work.edit_visit')) : @js(__('my_work.add_visit'))"></h4>
                                        <button type="button" @click="visitFormOpen = false; visitError = ''"
                                            class="min-h-11 min-w-11 rounded-xl border border-slate-200 bg-white"
                                            aria-label="{{ __('my_work.close_form') }}">✕</button>
                                    </div>
                                    <p x-show="visitError" role="alert"
                                        class="rounded-xl bg-rose-50 p-3 text-sm text-rose-800" x-text="visitError"></p>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <label class="text-sm text-slate-700">{{ __('my_work.visit_type') }} *<select
                                                x-ref="visitTypeInput" x-model="visitForm.visit_type" required
                                                class="mt-1 w-full rounded-xl border-slate-300">
                                                @foreach(__('my_work.visit_types') as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach
                                            </select></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.datetime') }} *<input type="datetime-local"
                                                x-model="visitForm.occurred_at" required
                                                class="mt-1 w-full rounded-xl border-slate-300"></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.internal_participants') }} *<input
                                                x-model="visitForm.internal_participants" required maxlength="500"
                                                class="mt-1 w-full rounded-xl border-slate-300"></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.customer_participants') }}<input
                                                x-model="visitForm.customer_participants" maxlength="500"
                                                class="mt-1 w-full rounded-xl border-slate-300"></label>
                                        <label class="text-sm text-slate-700 sm:col-span-2">{{ __('my_work.location') }}<input
                                                x-model="visitForm.location" maxlength="500"
                                                class="mt-1 w-full rounded-xl border-slate-300"></label>
                                        <label class="text-sm text-slate-700 sm:col-span-2">{{ __('my_work.visit_summary') }} *<textarea
                                                x-model="visitForm.summary" required rows="3" maxlength="10000"
                                                class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                                        <label class="text-sm text-slate-700 sm:col-span-2">{{ __('my_work.site_findings') }}<textarea
                                                x-model="visitForm.site_findings" rows="2" maxlength="10000"
                                                class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.decisions') }}<textarea
                                                x-model="visitForm.decisions" rows="2" maxlength="10000"
                                                class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.risks') }}<textarea x-model="visitForm.risks"
                                                rows="2" maxlength="10000"
                                                class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                                        <label class="text-sm text-slate-700 sm:col-span-2">{{ __('my_work.next_action') }} *<textarea
                                                x-model="visitForm.next_steps" required rows="3" maxlength="10000"
                                                class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.next_due') }}<input type="datetime-local"
                                                x-model="visitForm.next_action_at"
                                                class="mt-1 w-full rounded-xl border-slate-300"></label>
                                    </div>
                                    <div class="flex justify-end gap-3"><button type="button"
                                            @click="visitFormOpen = false; visitError = ''"
                                            class="min-h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold">{{ __('my_work.cancel') }}</button><button
                                            type="submit" :disabled="visitSaving"
                                            class="min-h-11 rounded-xl bg-cyan-700 px-5 text-sm font-semibold text-white disabled:opacity-60"
                                            x-text="visitSaving ? @js(__('my_work.saving')) : @js(__('my_work.save_visit'))"></button></div>
                                </form>
                            </section>
                            <section class="rounded-2xl border border-slate-200 bg-white p-5"
                                aria-label="{{ __('my_work.files_photos') }}">
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ __('my_work.files_photos') }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('my_work.files_note') }}</p>
                                </div>
                                <form @submit.prevent="uploadAttachment()"
                                    class="mt-5 space-y-4 rounded-2xl border border-cyan-100 bg-cyan-50/50 p-4">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <label class="text-sm text-slate-700">{{ __('my_work.file_type') }}<select x-model="uploadCategory"
                                                class="mt-1 w-full rounded-xl border-slate-300"><template
                                                    x-for="[value, label] in Object.entries(fileCategories)" :key="value">
                                                    <option :value="value" x-text="label"></option>
                                                </template></select></label>
                                        <label class="text-sm text-slate-700">{{ __('my_work.file_note') }}<input x-model="uploadNote"
                                                maxlength="1000" class="mt-1 w-full rounded-xl border-slate-300"
                                                placeholder="{{ __('my_work.file_note_example') }}"></label>
                                    </div>
                                    <label
                                        class="block cursor-pointer rounded-2xl border-2 border-dashed border-cyan-300 bg-white p-5 text-center hover:bg-cyan-50">
                                        <span class="block text-2xl text-cyan-700" aria-hidden="true">↥</span>
                                        <span class="mt-1 block break-all text-sm font-semibold text-slate-900"
                                            x-text="uploadFile?.name || @js(__('my_work.choose_file'))"></span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ __('my_work.file_limit') }}</span>
                                        <input x-ref="uploadInput" type="file" class="sr-only"
                                            accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                            @change="uploadFile = $event.target.files?.[0] || null; uploadError = ''">
                                    </label>
                                    <p x-show="uploadError" role="alert"
                                        class="rounded-xl bg-rose-50 p-3 text-sm text-rose-800" x-text="uploadError"></p>
                                    <div class="flex justify-end"><button type="submit"
                                            :disabled="!uploadFile || uploadSaving"
                                            class="min-h-11 rounded-xl bg-cyan-700 px-5 py-2 text-sm font-semibold text-white hover:bg-cyan-800 disabled:opacity-50"
                                            x-text="uploadSaving ? @js(__('my_work.uploading')) : @js(__('my_work.upload'))"></button></div>
                                </form>
                                <template x-if="(detail.sections.attachments ?? []).length === 0">
                                    <p
                                        class="mt-5 rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">
                                        {{ __('my_work.no_files') }}</p>
                                </template>
                                <div x-show="(detail.sections.attachments ?? []).length > 0" class="mt-5 space-y-5">
                                    <template x-for="group in fileGroups()" :key="group.label">
                                        <section x-show="group.files.length">
                                            <h4 class="mb-3 text-sm font-bold text-slate-900"
                                                x-text="`${group.label} (${group.files.length})`"></h4>
                                            <ul class="space-y-3"><template x-for="file in group.files"
                                                    :key="`${file.origin}-${file.id}`">
                                                    <li class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                                            <strong class="break-all text-sm text-slate-900"
                                                                x-text="display(file.file_name)"></strong><span
                                                                class="text-xs text-slate-500"
                                                                x-text="display(file.created_at)"></span></div>
                                                        <p class="mt-2 text-xs text-slate-600"><span
                                                                x-text="fileCategories[file.category] || display(file.category)"></span>
                                                            · <span x-text="formatBytes(file.size_bytes)"></span> · <span
                                                                x-text="file.origin === 'current' ? @js(__('my_work.new_file')) : @js(__('my_work.legacy_file'))"></span><span
                                                                x-show="file.uploaded_by"
                                                                x-text="' · ' + @js(__('my_work.uploaded_by', ['name' => ':name'])).replace(':name', file.uploaded_by)"></span></p>
                                                        <p x-show="file.note"
                                                            class="mt-2 whitespace-pre-wrap break-words text-sm text-slate-700"
                                                            x-text="file.note"></p>
                                                        <a x-show="file.content_url" :href="file.content_url"
                                                            target="_blank" rel="noopener noreferrer"
                                                            class="mt-3 inline-flex min-h-11 items-center rounded-xl border border-cyan-200 bg-white px-3 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-50">{{ __('my_work.open_file') }}
                                                            ↗</a>
                                                        <p x-show="!file.content_url" class="mt-3 text-xs text-amber-700">
                                                            {{ __('my_work.legacy_no_file') }}</p>
                                                    </li>
                                                </template></ul>
                                        </section>
                                    </template>
                                </div>
                            </section>
                        </div>
                    </template>
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            aria-label="{{ __('my_work.task_list') }}">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-950">{{ __('my_work.sorted_due') }}</h2>
                <span class="text-sm text-slate-500" x-text="@js(__('my_work.task_count', ['count' => ':count'])).replace(':count', summary.total)"></span>
            </div>
            <template x-if="items.length > 0">
                <div>
                    <template x-for="item in items" :key="`${item.type}/${item.source_id}`">
                        <article
                            class="grid gap-3 border-b border-slate-100 p-5 last:border-0 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="taskTone(item.type)" x-text="taskTypeLabel(item.type)"></span>
                                    <span x-show="item.overdue" class="text-xs font-semibold text-rose-700">{{ __('my_work.overdue') }}</span>
                                </div>
                                <h3 class="mt-2 font-semibold text-slate-950" x-text="item.title"></h3>
                                <p class="mt-1 text-sm text-slate-700" x-text="item.site_name"></p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('my_work.due') }} <span x-text="item.due_label"></span> · {{ __('my_work.origin') }}
                                    <span x-text="item.origin"></span></p>
                            </div>
                            <template x-if="item.type === 'follow_up'"><button type="button" @click="openFollowUp(item)"
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-cyan-200 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-600">{{ __('my_work.save_visit') }}
                                    <span aria-hidden="true" class="ml-2">→</span></button></template>
                            <template x-if="item.type !== 'follow_up'"><a :href="item.url"
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-cyan-200 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-600">{{ __('my_work.view_details') }}
                                    <span aria-hidden="true" class="ml-2">↗</span></a></template>
                        </article>
                    </template>
                </div>
            </template>
            <template x-if="items.length === 0">
                <div class="px-5 py-14 text-center">
                    <p class="font-semibold text-slate-900">{{ __('my_work.no_tasks') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('my_work.no_tasks_note') }}</p>
                </div>
            </template>
        </section>
    </div>
@endsection
