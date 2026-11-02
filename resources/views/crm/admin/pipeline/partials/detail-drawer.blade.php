    <div x-cloak x-show="isOpen" class="crm-overlay-layer fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[3px]" @click="close()"></div>
        <section x-ref="dialog" role="dialog" aria-modal="true" aria-labelledby="pipeline-detail-title"
            @keydown.tab="trapFocus($event)"
            class="absolute inset-y-0 right-0 flex w-full max-w-4xl flex-col bg-slate-50 shadow-2xl">
            <header class="shrink-0 border-b border-slate-200 bg-white px-4 pt-5 sm:px-7">
                <div class="flex items-start justify-between gap-4 pb-5">
                    <div class="min-w-0">
                        <p class="crm-eyebrow">GSB EV · BUSINESS DEVELOPMENT</p>
                        <h2 id="pipeline-detail-title"
                            class="mt-1 break-words text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
                            x-text="detail.site_name || @js(__('pipeline_detail.opportunity'))"></h2>
                        <p class="mt-1 text-sm text-slate-500" x-text="detail.area || @js(__('pipeline_page.no_area'))"></p>
                        <div x-show="detail.source_id" class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-cyan-50 px-3 py-1 font-semibold text-cyan-800"
                                x-text="detail.stage_label"></span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"
                                x-text="@js(__('pipeline_detail.priority')) + displayValue(detail.priority_label)"></span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"
                                x-text="@js(__('pipeline_detail.probability')) + displayValue(detail.probability_display)"></span>
                        </div>
                        @if(auth()->user()->hasPermission('pipeline.stage'))
                        <div x-show="detail.source_id" class="mt-3 flex flex-wrap items-end gap-2">
                            <label class="min-w-[180px] flex-1 text-xs font-semibold text-slate-700">{{ __('pipeline_detail.sales_stage') }}
                                <select x-model="stageChoice" :disabled="stageSaving"
                                    class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    <option value="" disabled>{{ __('pipeline_detail.choose_stage') }}</option>
                                    <template x-if="detail.stage_code && !@js(\App\Crm\Support\SalesStages::codes()).includes(detail.stage_code)">
                                        <option :value="detail.stage_code" x-text="detail.stage_label" disabled></option>
                                    </template>
                                    @foreach(__('pipeline_status.stages') as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="button" @click="saveStage()" :disabled="stageSaving || !stageChoice || stageChoice === detail.stage_code"
                                class="rounded-xl bg-cyan-700 px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">{{ __('pipeline_detail.save_stage') }}</button>
                        </div>
                        <p x-show="stageError" role="alert" class="mt-2 text-sm text-rose-700" x-text="stageError"></p>
                        @endif
                    </div>
                    <div class="flex shrink-0 items-center gap-2">@if(auth()->user()->hasPermission('pipeline.details'))<button x-show="!loading && detail.source_id" type="button" @click="startUnifiedEdit()"
                        class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-teal-700 hover:to-cyan-700">{{ __('pipeline_detail.edit') }}</button>
                    @endif
                    <button x-ref="closeButton" type="button" @click="close()" aria-label="{{ __('my_work.close_details') }}"
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600">✕</button></div>
                </div>
                <div role="tablist" aria-label="{{ __('pipeline_detail.tabs_label') }}" @keydown.arrow-right.prevent="moveTab(1)"
                    @keydown.arrow-left.prevent="moveTab(-1)"
                    class="-mx-4 flex gap-1 overflow-x-auto px-4 pb-0 sm:-mx-7 sm:px-7">
                    @foreach([['profile', __('pipeline_detail.tabs.profile')], ['business', __('pipeline_detail.tabs.business')], ['technical', __('pipeline_detail.tabs.technical')], ['activity', __('pipeline_detail.tabs.activity')], ['files', __('pipeline_detail.tabs.files')]] as [$tabId, $tabLabel])
                        <button type="button" role="tab" id="pipeline-tab-{{ $tabId }}"
                            aria-controls="pipeline-panel-{{ $tabId }}" :aria-selected="activeTab === '{{ $tabId }}'"
                            :tabindex="activeTab === '{{ $tabId }}' ? 0 : -1" @click="activeTab = '{{ $tabId }}'"
                            class="shrink-0 border-b-2 px-3 py-3 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-cyan-600"
                            :class="activeTab === '{{ $tabId }}' ? 'border-cyan-600 text-cyan-800' : 'border-transparent text-slate-500 hover:text-slate-900'">{{ $tabLabel }}</button>
                    @endforeach
                </div>
            </header>
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-7">
                <p x-show="loading" role="status" class="rounded-2xl bg-white p-5 text-sm">{{ __('my_work.loading') }}</p>
                <div x-show="error" role="alert"
                    class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-800"><span
                        x-text="error"></span><button type="button" @click="reload()"
                        class="ml-3 font-semibold underline">{{ __('pipeline_detail.retry') }}</button></div>
                <div x-show="!loading && !error && detail.source_id" class="space-y-5">
                    <div x-show="detail.unmatched"
                        class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {{ __('pipeline_detail.unmatched') }}
                    </div>
                    <section x-show="activeTab === 'profile'" role="tabpanel" id="pipeline-panel-profile"
                        aria-labelledby="pipeline-tab-profile" tabindex="0" class="space-y-5">
                        <div x-show="sections.profile?._legacy && (sections.profile._legacy.site_name !== sections.profile.site_name || sections.profile._legacy.area !== sections.profile.area || sections.profile._legacy.assigned_to !== sections.profile.assigned_to)"
                            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                            <p class="font-semibold">{{ __('pipeline_detail.current_differs') }}</p>
                            <p class="mt-2">{{ __('pipeline_detail.current_data') }} <span
                                    x-text="displayValue(sections.profile?.current_site?.site_name)"></span> · <span
                                    x-text="displayValue(sections.profile?.current_site?.area)"></span></p>
                            <p class="mt-1">{{ __('pipeline_detail.imported_data') }} <span
                                    x-text="displayValue(sections.profile?._legacy?.site_name)"></span> · <span
                                    x-text="displayValue(sections.profile?._legacy?.area)"></span></p>
                            <p class="mt-2">{{ __('pipeline_detail.current_owner') }} <span
                                    x-text="displayValue(sections.profile?.current_site?.owner)"></span></p>
                            <p class="mt-1">{{ __('pipeline_detail.imported_owner') }} <span
                                    x-text="displayValue(sections.profile?._legacy?.assigned_to)"></span></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">01 / PROFILE
                                    </p>
                                    <h3 class="mt-1 text-lg font-bold text-slate-950">{{ __('pipeline_detail.site_contacts') }}</h3>
                                </div><span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600" x-text="sections.profile?._source === 'current' ? @js(__('pipeline_detail.current')) : @js(__('pipeline_detail.legacy'))"></span>
                            </div>
                            <p x-show="sections.profile?._updated_at" class="mt-3 text-xs text-slate-500"
                                x-text="@js(__('pipeline_detail.last_edit', ['date' => ':date', 'name' => ':name'])).replace(':date', formatDate(sections.profile?._updated_at)).replace(':name', sections.profile?._updated_by || @js(__('pipeline_detail.system_user')))"></p>
                            <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                                <template x-for="field in profileFields" :key="field.key">
                                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-medium text-slate-500" x-text="field.label"></dt>
                                        <dd class="mt-1 break-words whitespace-pre-wrap text-sm font-medium text-slate-900"
                                            x-text="fieldValue(sections.profile, field)"></dd>
                                    </div>
                                </template>
                            </dl>
                            <a x-show="sections.profile?.map_url" :href="sections.profile?.map_url || null"
                                target="_blank" rel="noopener noreferrer"
                                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-800 hover:bg-cyan-100">{{ __('pipeline_detail.view_map') }}
                                <span aria-hidden="true">↗</span></a>
                        </div>
                        <div
                            class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-xs text-slate-500">
                            <span>{{ __('pipeline_detail.legacy_id') }} <span class="font-mono"
                                    x-text="detail.source_id"></span></span>@if(auth()->user()->hasPermission('sites.view'))<template x-if="detail.site_id"><a
                                    :href="siteUrl + '&site=' + encodeURIComponent(detail.site_id)"
                                    class="font-semibold text-cyan-700">{{ __('pipeline_detail.view_new_site') }}</a></template>@endif</div>
                    </section>
                    <section x-show="activeTab === 'business'" role="tabpanel" id="pipeline-panel-business"
                        aria-labelledby="pipeline-tab-business" tabindex="0"
                        class="space-y-5">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">02 / BUSINESS</p>
                                    <h3 class="mt-1 text-lg font-bold text-slate-950">{{ __('pipeline_detail.tabs.business') }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('pipeline_sections.business_note') }}</p></div>
                                @if(auth()->user()->hasPermission('pipeline.business'))<button type="button" @click="startUnifiedEdit(); editorTab = 'business'"
                                    class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">{{ __('pipeline_detail.edit') }}</button>
                                @endif
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full px-3 py-1 font-semibold"
                                    :class="sections.business?._source === 'current' ? 'bg-teal-50 text-teal-800' : 'bg-slate-100 text-slate-600'"
                                    x-text="sections.business?._source === 'current' ? @js(__('pipeline_sections.current_business')) : @js(__('pipeline_sections.imported_business'))"></span>
                                <span x-show="sections.business?._updated_at" class="text-slate-500"
                                    x-text="@js(__('pipeline_detail.last_edit', ['date' => ':date', 'name' => ':name'])).replace(':date', formatDate(sections.business?._updated_at)).replace(':name', sections.business?._updated_by || @js(__('pipeline_detail.system_user')))"></span>
                            </div>
                            <p x-show="businessSuccess" role="status" class="mt-3 rounded-xl bg-teal-50 p-3 text-sm text-teal-800" x-text="businessSuccess"></p>
                        </div>
                        <template x-if="!businessEditing"><div class="space-y-4"><template x-for="group in businessGroups" :key="group.title">
                            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <h4 class="text-sm font-bold text-slate-900" x-text="group.title"></h4>
                                <dl class="mt-4 grid gap-3 sm:grid-cols-2"><template x-for="field in businessGroupFields(group)" :key="field.key">
                                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-medium text-slate-500" x-text="field.label"></dt>
                                        <dd class="mt-1 break-words whitespace-pre-wrap text-sm font-semibold text-slate-900" x-text="businessFieldValue(sections.business, field)"></dd>
                                        <p x-show="businessLegacyDifferent(field.key)" class="mt-2 border-t border-slate-200 pt-2 text-xs text-slate-500">
                                            {{ __('pipeline_detail.imported_data') }} <span x-text="businessFieldValue(sections.business?._legacy, field)"></span></p>
                                    </div>
                                </template></dl>
                            </section>
                        </template></div></template>
                        <form x-show="false" @submit.prevent="saveBusiness()" class="space-y-4">
                            <template x-for="group in businessGroups" :key="group.title"><section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <h4 class="text-sm font-bold text-slate-900" x-text="group.title"></h4>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2"><template x-for="field in businessGroupFields(group)" :key="field.key">
                                    <label class="block min-w-0 text-sm font-medium text-slate-700"><span x-text="field.label"></span>
                                        <template x-if="businessOptions[field.key]"><select x-model="businessForm[field.key]" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-900">
                                            <template x-for="option in businessOptions[field.key]" :key="option[0]"><option :value="option[0]" x-text="option[1]"></option></template>
                                        </select></template>
                                        <template x-if="field.key === 'notes'"><textarea x-model="businessForm[field.key]" rows="4" maxlength="10000" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-900"></textarea></template>
                                        <template x-if="!businessOptions[field.key] && field.key !== 'notes'"><input x-model="businessForm[field.key]" :type="businessInputType(field.key)"
                                            :step="businessInputType(field.key) === 'number' ? (field.key === 'parking_spaces' ? '1' : 'any') : null"
                                            :min="businessInputType(field.key) === 'number' ? '0' : null"
                                            class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-900"></template>
                                    </label>
                                </template></div>
                            </section></template>
                            <p x-show="businessError" role="alert" class="rounded-xl bg-rose-50 p-3 text-sm text-rose-800" x-text="businessError"></p>
                            <div class="sticky bottom-0 flex flex-wrap justify-end gap-2 border-t border-slate-200 bg-slate-50/95 py-3 backdrop-blur">
                                <button type="button" @click="cancelBusinessEdit()" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold">{{ __('my_work.cancel') }}</button>
                                <button type="submit" :disabled="businessSaving" class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
                                    x-text="businessSaving ? @js(__('my_work.saving')) : @js(__('pipeline_sections.save_business'))"></button>
                            </div>
                        </form>
                    </section>
                    <section x-show="activeTab === 'technical'" role="tabpanel" id="pipeline-panel-technical"
                        aria-labelledby="pipeline-tab-technical" tabindex="0" class="space-y-5">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">03 / ENGINEERING</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-950">{{ __('pipeline_sections.technical') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('pipeline_sections.technical_note') }}
                            </p>
                            <p x-show="sections.technical?._updated_at" class="mt-2 text-xs text-slate-500"
                                x-text="@js(__('pipeline_detail.last_edit', ['date' => ':date', 'name' => ':name'])).replace(':date', formatDate(sections.technical?._updated_at)).replace(':name', sections.technical?._updated_by || @js(__('pipeline_detail.system_user')))"></p>
                            <dl class="mt-5 grid gap-3 sm:grid-cols-2"><template x-for="field in technicalFields"
                                    :key="field.key">
                                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-medium text-slate-500" x-text="field.label"></dt>
                                        <dd class="mt-1 break-words whitespace-pre-wrap text-sm font-medium text-slate-900"
                                            x-text="fieldValue(sections.technical, field)"></dd>
                                    </div>
                                </template></dl>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-bold text-slate-950">{{ __('pipeline_sections.linked_project') }}</h3>
                            <p x-show="!sections.technical?.project"
                                class="mt-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                                {{ __('pipeline_sections.no_project') }}</p>
                            <dl x-show="sections.technical?.project" class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <dt class="text-xs text-slate-500">{{ __('pipeline_sections.project_code') }}</dt>
                                    <dd class="mt-1 break-words text-sm font-semibold"
                                        x-text="displayValue(sections.technical?.project?.project_code)"></dd>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <dt class="text-xs text-slate-500">{{ __('pipeline_sections.engineering_stage') }}</dt>
                                    <dd class="mt-1 break-words text-sm font-semibold"
                                        x-text="@js(__('stages.engineering'))[({ WAITING_SURVEY: 'AWAITING_SURVEY', OPERATIONAL: 'LIVE' })[sections.technical?.project?.engineering_stage] || sections.technical?.project?.engineering_stage] || displayValue(sections.technical?.project?.engineering_stage)"></dd>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <dt class="text-xs text-slate-500">{{ __('pipeline_sections.sync_status') }}</dt>
                                    <dd class="mt-1 break-words text-sm font-semibold"
                                        x-text="sections.technical?.project?.origin === 'current' && sections.technical?.project?.sync_status === 'pending_connection' ? @js(__('pipeline_sections.pending_connection')) : @js(__('pipeline_sections.legacy_connection'))"></dd>
                                </div>
                            </dl>
                        </div>
                    </section>
                    <section x-show="activeTab === 'activity'" role="tabpanel" id="pipeline-panel-activity"
                        aria-labelledby="pipeline-tab-activity" tabindex="0" class="space-y-5">
                        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm">
                            <h3 class="text-base font-bold text-slate-950">{{ __('pipeline_page.next_action') }}</h3>
                            <p class="mt-2 whitespace-pre-wrap break-words text-sm text-slate-800"
                                x-text="displayValue(sections.profile?.next_action)"></p>
                            <p class="mt-2 text-xs text-slate-600">{{ __('my_work.due_date') }}: <span
                                    x-text="formatDate(sections.profile?.next_action_at)"></span></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">04 / FOLLOW-UP</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-950">{{ __('pipeline_sections.contact_history') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('pipeline_sections.history_note') }}</p>
                            <p x-show="!sections.activities?.length"
                                class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                                {{ __('pipeline_sections.no_history') }}</p>
                            <ol x-show="sections.activities?.length" class="mt-5 space-y-3">
                                <template x-for="activity in (sections.activities || [])" :key="activity.id">
                                    <li class="relative rounded-xl border border-slate-200 bg-slate-50 p-4 pl-5"><span
                                            class="absolute bottom-4 left-0 top-4 w-1 rounded-full bg-cyan-500"></span>
                                        <div class="flex flex-wrap items-center justify-between gap-2"><strong
                                                class="text-sm text-slate-950"
                                                x-text="displayValue(activity.activity_type)"></strong><time
                                                class="text-xs text-slate-500"
                                                x-text="formatDate(activity.occurred_at)"></time></div>
                                        <p class="mt-2 whitespace-pre-wrap break-words text-sm text-slate-700"
                                            x-text="displayValue(activity.summary)"></p>
                                    </li>
                                </template>
                            </ol>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-950">{{ __('my_work.visits') }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('pipeline_sections.visit_note') }}
                                    </p>
                                </div><button type="button" @click="openVisit()"
                                    class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-teal-700 hover:to-cyan-700">＋
                                    {{ __('my_work.add_record') }}</button>
                            </div>
                            <p x-show="!sections.visits?.length"
                                class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('pipeline_sections.no_visits') }}
                                <button type="button" @click="openVisit()"
                                    class="font-semibold text-cyan-700 underline">{{ __('pipeline_sections.first_visit') }}</button></p>
                            <div x-show="sections.visits?.length" class="mt-5 space-y-4"><template
                                    x-for="visit in (sections.visits || [])" :key="visit.id">
                                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex items-center gap-2"><strong class="text-sm text-slate-950"
                                                    x-text="visitTypes[visit.visit_type] || displayValue(visit.visit_type)"></strong><span
                                                    class="rounded-full bg-white px-2 py-1 text-[11px] text-slate-500"
                                                    x-text="visit.origin === 'current' ? @js(__('pipeline_sections.new_record')) : @js(__('my_work.legacy_file'))"></span>
                                            </div>
                                            <div class="flex items-center gap-2"><time class="text-xs text-slate-500"
                                                    x-text="formatDate(visit.occurred_at)"></time><button
                                                    x-show="visit.editable" type="button" @click="openVisit(visit)"
                                                    class="rounded-lg border border-cyan-200 bg-white px-2 py-1 text-xs font-semibold text-cyan-800">{{ __('my_work.edit') }}</button>
                                            </div>
                                        </div>
                                        <dl class="mt-4 grid gap-3 sm:grid-cols-2"><template
                                                x-for="field in visitFields" :key="field.key">
                                                <div class="min-w-0 rounded-lg bg-white p-3">
                                                    <dt class="text-xs text-slate-500" x-text="field.label"></dt>
                                                    <dd class="mt-1 whitespace-pre-wrap break-words text-sm text-slate-800"
                                                        x-text="fieldValue(visit, field)"></dd>
                                                </div>
                                            </template></dl>
                                    </article>
                                </template></div>
                        </div>
                        <div x-show="visitFormOpen" x-cloak
                            class="crm-overlay-layer fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 p-3 sm:p-6"
                            @click.self="visitFormOpen = false" @keydown.escape.stop="visitFormOpen = false">
                            <form x-ref="visitDialog" @submit.prevent="saveVisit()" role="dialog" aria-modal="true"
                                aria-labelledby="crm-visit-form-title"
                                class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-7">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="crm-eyebrow">GSB EV · FOLLOW-UP</p>
                                        <h4 id="crm-visit-form-title" class="mt-1 text-xl font-bold"
                                            x-text="editingVisitId ? @js(__('my_work.edit_visit')) : @js(__('my_work.add_visit'))">
                                        </h4>
                                    </div><button type="button" @click="visitFormOpen = false" aria-label="{{ __('my_work.close_form') }}"
                                        class="rounded-lg border px-3 py-2">✕</button>
                                </div>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <label class="text-sm font-medium">{{ __('pipeline_sections.record_type') }} *<select x-ref="visitTypeInput"
                                            x-model="visitForm.visit_type" required
                                            class="mt-1 w-full rounded-xl border-slate-200"><template
                                                x-for="[value, label] in Object.entries(visitTypes)" :key="value">
                                                <option :value="value" x-text="label"></option>
                                            </template></select></label>
                                    <label class="text-sm font-medium">{{ __('my_work.datetime') }} *<input
                                            x-model="visitForm.occurred_at" required type="datetime-local"
                                            class="mt-1 w-full rounded-xl border-slate-200"></label>
                                    <label class="text-sm font-medium">{{ __('pipeline_sections.our_participants') }} *<input
                                            x-model="visitForm.internal_participants" required maxlength="500"
                                            class="mt-1 w-full rounded-xl border-slate-200"></label>
                                    <label class="text-sm font-medium">{{ __('pipeline_sections.customer_participants') }}<input
                                            x-model="visitForm.customer_participants" maxlength="500"
                                            class="mt-1 w-full rounded-xl border-slate-200"></label>
                                    <label class="text-sm font-medium sm:col-span-2">{{ __('pipeline_sections.contact_location') }}<input
                                            x-model="visitForm.location" maxlength="500"
                                            class="mt-1 w-full rounded-xl border-slate-200"></label>
                                    <label class="text-sm font-medium sm:col-span-2">{{ __('my_work.visit_summary') }} *<textarea
                                            x-model="visitForm.summary" required rows="3"
                                            class="mt-1 w-full rounded-xl border-slate-200"></textarea></label>
                                    <label class="text-sm font-medium sm:col-span-2">{{ __('my_work.site_findings') }}<textarea
                                            x-model="visitForm.site_findings" rows="2"
                                            class="mt-1 w-full rounded-xl border-slate-200"></textarea></label>
                                    <label class="text-sm font-medium">{{ __('pipeline_sections.confirmations') }}<textarea
                                            x-model="visitForm.decisions" rows="2"
                                            class="mt-1 w-full rounded-xl border-slate-200"></textarea></label>
                                    <label class="text-sm font-medium">{{ __('pipeline_sections.risks') }}<textarea
                                            x-model="visitForm.risks" rows="2"
                                            class="mt-1 w-full rounded-xl border-slate-200"></textarea></label>
                                    <label class="text-sm font-medium sm:col-span-2">{{ __('pipeline_sections.next_steps') }} *<textarea
                                            x-model="visitForm.next_steps" required rows="2"
                                            class="mt-1 w-full rounded-xl border-slate-200"></textarea></label>
                                    <label class="text-sm font-medium">{{ __('my_work.next_due') }}<input
                                            x-model="visitForm.next_action_at" type="datetime-local"
                                            class="mt-1 w-full rounded-xl border-slate-200"></label>
                                </div>
                                <p x-show="visitError" role="alert"
                                    class="mt-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800" x-text="visitError">
                                </p>
                                <div class="mt-5 flex justify-end gap-2"><button type="button"
                                        @click="visitFormOpen = false"
                                        class="rounded-xl border px-4 py-2 text-sm font-semibold">{{ __('my_work.cancel') }}</button><button
                                        type="submit" :disabled="visitSaving"
                                        class="rounded-xl bg-cyan-700 px-5 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                        x-text="visitSaving ? @js(__('my_work.saving')) : @js(__('pipeline_sections.save'))"></button></div>
                            </form>
                        </div>
                    </section>
                    <section x-show="activeTab === 'files'" role="tabpanel" id="pipeline-panel-files"
                        aria-labelledby="pipeline-tab-files" tabindex="0"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">05 / FILES</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-950">{{ __('my_work.files_photos') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('my_work.files_note') }}</p>
                        <form @submit.prevent="uploadAttachment()"
                            class="mt-5 rounded-2xl border border-cyan-100 bg-cyan-50/50 p-4">
                            <h4 class="font-semibold text-slate-900">{{ __('my_work.upload') }}</h4>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2"><label
                                    class="text-sm font-medium">{{ __('my_work.file_type') }}<select x-model="uploadCategory"
                                        class="mt-1 w-full rounded-xl border-slate-200"><template
                                            x-for="[value, label] in Object.entries(fileCategories)" :key="value">
                                            <option :value="value" x-text="label"></option>
                                        </template></select></label><label
                                    class="text-sm font-medium">{{ __('my_work.file_note') }}<input x-model="uploadNote" maxlength="1000"
                                        class="mt-1 w-full rounded-xl border-slate-200"
                                        placeholder="{{ __('my_work.file_note_example') }}"></label></div>
                            <label
                                class="mt-4 block cursor-pointer rounded-2xl border-2 border-dashed border-cyan-300 bg-white p-6 text-center transition hover:bg-cyan-50"><span
                                    class="block text-2xl text-cyan-700">↥</span><span
                                    class="mt-1 block text-sm font-semibold"
                                    x-text="uploadFile?.name || @js(__('my_work.choose_file'))"></span><span
                                    class="mt-1 block text-xs text-slate-500">{{ __('my_work.file_limit') }}</span><input x-ref="uploadInput" type="file"
                                    class="sr-only"
                                    accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                    @change="uploadFile = $event.target.files?.[0] || null"></label>
                            <p x-show="uploadError" role="alert"
                                class="mt-3 rounded-xl bg-rose-50 p-3 text-sm text-rose-800" x-text="uploadError"></p>
                            <div class="mt-4 flex justify-end"><button type="submit"
                                    :disabled="!uploadFile || uploadSaving"
                                    class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                    x-text="uploadSaving ? @js(__('my_work.uploading')) : @js(__('my_work.upload'))"></button></div>
                        </form>
                        <div x-show="!sections.attachments?.length"
                            class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                            <p class="font-semibold text-slate-800">{{ __('my_work.no_files') }}</p>
                        </div>
                        <div x-show="sections.attachments?.length" class="mt-5 space-y-6"><template
                                x-for="group in fileGroups()" :key="group.label">
                                <section x-show="group.files.length">
                                    <h4 class="mb-3 text-sm font-bold text-slate-900"
                                        x-text="`${group.label} (${group.files.length})`"></h4>
                                    <ul class="space-y-3"><template x-for="file in group.files"
                                            :key="`${file.origin}-${file.id}`">
                                            <li class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="flex flex-wrap justify-between gap-2"><strong
                                                        class="break-all text-sm text-slate-900"
                                                        x-text="displayValue(file.file_name)"></strong><span
                                                        class="text-xs text-slate-500"
                                                        x-text="formatDate(file.created_at)"></span></div>
                                                <p class="mt-2 text-xs text-slate-600"><span
                                                        x-text="fileCategories[file.category] || displayValue(file.category)"></span>
                                                    · <span x-text="formatBytes(file.size_bytes)"></span> · <span
                                                        x-text="file.origin === 'current' ? @js(__('my_work.new_file')) : @js(__('my_work.legacy_file'))"></span><span
                                                        x-show="file.uploaded_by"
                                                        x-text="' · ' + @js(__('my_work.uploaded_by', ['name' => ':name'])).replace(':name', file.uploaded_by)"></span></p>
                                                <p x-show="file.note"
                                                    class="mt-2 whitespace-pre-wrap break-words text-sm text-slate-700"
                                                    x-text="file.note"></p><a x-show="file.content_url"
                                                    :href="file.content_url" target="_blank" rel="noopener noreferrer"
                                                    class="mt-3 inline-flex rounded-lg bg-white px-3 py-2 text-sm font-semibold text-cyan-800 underline">{{ __('my_work.open_file') }}
                                                    ↗</a>
                                                <p x-show="!file.content_url" class="mt-3 text-xs text-amber-700">
                                                    {{ __('my_work.legacy_no_file') }}</p>
                                            </li>
                                        </template></ul>
                                </section>
                            </template></div>
                    </section>
                </div>
            </div>
            <div x-show="editorOpen" x-cloak class="crm-overlay-layer fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/70 p-0 backdrop-blur-sm sm:p-5"
                @click.self="cancelUnifiedEdit()" @keydown.escape.stop="cancelUnifiedEdit()">
                <form x-ref="editorDialog" @submit.prevent="saveUnified()" role="dialog" aria-modal="true" aria-labelledby="crm-editor-title"
                    class="flex h-full max-h-[96vh] w-full max-w-5xl flex-col overflow-hidden bg-slate-50 shadow-2xl sm:rounded-3xl">
                    <header class="shrink-0 border-b border-slate-200 bg-white px-5 py-4 sm:px-7">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0"><p class="crm-eyebrow">GSB EV · ONE-STEP EDIT</p>
                                <h3 id="crm-editor-title" class="mt-1 text-xl font-bold text-slate-950 sm:text-2xl">{{ __('pipeline_editor.title') }}</h3>
                                <p class="mt-1 truncate text-sm text-slate-500" x-text="detail.site_name"></p></div>
                            <button x-ref="editorClose" type="button" @click="cancelUnifiedEdit()" aria-label="{{ __('pipeline_editor.close') }}"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">✕</button>
                        </div>
                        <div role="tablist" aria-label="{{ __('pipeline_editor.tabs') }}" class="mt-5 flex gap-2 overflow-x-auto pb-1">
                            <template x-for="[id, label] in [['profile', @js(__('pipeline_editor.profile'))], ['business', @js(__('pipeline_editor.business'))], ['technical', @js(__('pipeline_editor.technical'))]]" :key="id">
                                <button type="button" role="tab" :aria-selected="editorTab === id" @click="editorTab = id"
                                    class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="editorTab === id ? 'bg-cyan-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    x-text="label"></button>
                            </template>
                        </div>
                    </header>
                    <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-7">
                        <p class="mb-5 rounded-xl border border-cyan-100 bg-cyan-50 p-3 text-sm text-cyan-900">{{ __('pipeline_editor.import_note') }}</p>
                        <section x-show="editorTab === 'profile'" role="tabpanel" class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2"><template x-for="field in [...profileFields.filter(item => !['next_action', 'next_action_at'].includes(item.key)), { label: @js(__('pipeline_editor.map')), key: 'map_url' }]" :key="field.key">
                                <label class="min-w-0 text-sm font-medium text-slate-700"><span x-text="field.label"></span><span x-show="field.key === 'site_name'"> *</span>
                                    <input x-model="editorForm.profile[field.key]" :type="field.key === 'email' ? 'email' : field.key === 'map_url' ? 'url' : 'text'"
                                        x-show="field.key !== 'location_type'"
                                        :disabled="field.key === 'assigned_to' && sections.profile?.current_site?.owner_user_id"
                                        :required="field.key === 'site_name'" :maxlength="field.key === 'map_url' ? 2048 : 500"
                                        class="mt-1 block w-full rounded-xl border-slate-200 bg-white text-slate-900 focus:border-cyan-500 focus:ring-cyan-500">
                                    <select x-show="field.key === 'location_type'" x-model="editorForm.profile.location_type" class="mt-1 block w-full rounded-xl border-slate-200 bg-white">
                                        <option value="">{{ __('sites.unspecified_type') }}</option>
                                        <template x-if="editorForm.profile.location_type && !@js(\App\Crm\Support\LocationTypes::codes()).includes(editorForm.profile.location_type)"><option :value="editorForm.profile.location_type" x-text="editorForm.profile.location_type"></option></template>
                                        @foreach(\App\Crm\Support\LocationTypes::codes() as $code)<option value="{{ $code }}">{{ __('location_types.'.$code) }}</option>@endforeach
                                    </select>
                                    <span x-show="field.key === 'assigned_to' && sections.profile?.current_site?.owner_user_id" class="mt-1 block text-xs text-slate-500">{{ __('pipeline_editor.change_owner') }}</span>
                                    <span x-show="editorErrors[`profile.${field.key}`]" class="mt-1 block text-xs text-rose-700" x-text="editorErrors[`profile.${field.key}`]"></span>
                                </label>
                            </template></div>
                        </section>
                        <section x-show="editorTab === 'business'" role="tabpanel" class="space-y-5">
                            <template x-for="group in businessGroups" :key="group.title"><div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <h4 class="font-bold text-slate-900" x-text="group.title"></h4>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2"><template x-for="field in businessGroupFields(group)" :key="field.key">
                                    <label class="min-w-0 text-sm font-medium text-slate-700"><span x-text="field.label"></span>
                                        <template x-if="businessOptions[field.key]"><select x-model="editorForm.business[field.key]" class="mt-1 block w-full rounded-xl border-slate-200 bg-white">
                                            <template x-for="option in businessOptions[field.key]" :key="option[0]"><option :value="option[0]" x-text="option[1]"></option></template>
                                        </select></template>
                                        <template x-if="field.key === 'notes'"><textarea x-model="editorForm.business.notes" rows="3" maxlength="10000" class="mt-1 block w-full rounded-xl border-slate-200"></textarea></template>
                                        <template x-if="!businessOptions[field.key] && field.key !== 'notes'"><input x-model="editorForm.business[field.key]"
                                            :type="field.key === 'contract_expiry' ? 'date' : ['rental_cost', 'revenue_share_amount', 'parking_spaces', 'deposit_amount', 'gsb_investment_percent', 'partner_investment_percent'].includes(field.key) ? 'number' : 'text'"
                                            :step="field.key === 'parking_spaces' ? '1' : 'any'" min="0" class="mt-1 block w-full rounded-xl border-slate-200 bg-white"></template>
                                        <span x-show="editorErrors[`business.${field.key}`]" class="mt-1 block text-xs text-rose-700" x-text="editorErrors[`business.${field.key}`]"></span>
                                    </label>
                                </template></div>
                            </div></template>
                        </section>
                        <section x-show="editorTab === 'technical'" role="tabpanel" class="space-y-5">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <h4 class="font-bold text-slate-900">{{ __('pipeline_editor.site_technical') }}</h4>
                                <p class="mt-1 text-xs text-slate-500">{{ __('pipeline_editor.stage_unchanged') }}</p>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2"><template x-for="field in technicalFields" :key="field.key">
                                    <label class="min-w-0 text-sm font-medium text-slate-700"><span x-text="field.label"></span>
                                        <input x-model="editorForm.technical[field.key]" :type="['transformer_kva', 'transformer_cost', 'requested_chargers', 'requested_connectors', 'charger_power_kw'].includes(field.key) ? 'number' : 'text'"
                                            :step="['requested_chargers', 'requested_connectors'].includes(field.key) ? '1' : 'any'" min="0"
                                            class="mt-1 block w-full rounded-xl border-slate-200 bg-white">
                                        <span x-show="editorErrors[`technical.${field.key}`]" class="mt-1 block text-xs text-rose-700" x-text="editorErrors[`technical.${field.key}`]"></span>
                                    </label>
                                </template></div>
                            </div>
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-5">
                                <h4 class="font-bold text-slate-900">{{ __('pipeline_page.next_action') }}</h4>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2"><label class="text-sm font-medium sm:col-span-2">{{ __('pipeline_editor.to_do') }}
                                    <textarea x-model="editorForm.profile.next_action" rows="3" maxlength="10000" class="mt-1 block w-full rounded-xl border-slate-200 bg-white"></textarea>
                                    <span x-show="editorErrors['profile.next_action']" class="mt-1 block text-xs text-rose-700" x-text="editorErrors['profile.next_action']"></span>
                                </label><label class="text-sm font-medium">{{ __('my_work.next_due') }}
                                    <input x-model="editorForm.profile.next_action_at" type="datetime-local" class="mt-1 block w-full rounded-xl border-slate-200 bg-white">
                                    <span x-show="editorErrors['profile.next_action_at']" class="mt-1 block text-xs text-rose-700" x-text="editorErrors['profile.next_action_at']"></span>
                                </label></div>
                            </div>
                        </section>
                    </div>
                    <footer class="shrink-0 border-t border-slate-200 bg-white px-5 py-4 sm:px-7">
                        <p x-show="editorError" role="alert" class="mb-3 rounded-xl bg-rose-50 p-3 text-sm text-rose-800" x-text="editorError"></p>
                        <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs text-slate-500">{{ __('pipeline_editor.atomic_save') }}</p>
                            <div class="flex gap-2"><button type="button" @click="cancelUnifiedEdit()" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold">{{ __('my_work.cancel') }}</button>
                                <button type="submit" :disabled="editorSaving" class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                    x-text="editorSaving ? @js(__('my_work.saving')) : @js(__('pipeline_editor.save_all'))"></button></div></div>
                    </footer>
                </form>
            </div>
            <footer class="shrink-0 border-t border-slate-200 bg-white px-4 py-3 text-xs text-slate-500 sm:px-7">
                {{ __('pipeline_editor.footer') }}</footer>
        </section>
    </div>
