@extends('crm.layouts.admin')

@section('content')
    <div class="crm-engineering" x-data="engineeringDirectory(@js(route('crm.admin.engineering.index')), @js($initialStages), @js(__('engineering_page')), @js(__('engineering_js')))"
        @keydown.escape.window="close()">
        <div class="crm-page-intro">
            <p class="crm-eyebrow">ENGINEERING HANDOFF</p>
            <h1>{{ __('engineering_page.title') }}</h1>
            <p>{{ __('engineering_page.description') }}</p>
        </div>
        <div class="mt-5 flex flex-wrap gap-3">
            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-5 py-3 text-sm font-semibold text-cyan-900">
                {{ __('engineering_page.survey_count', ['count' => count($surveyQueue)]) }}</div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-900">
                {{ __('engineering_page.unassigned_count', ['count' => count(array_filter($surveyQueue, fn($task) => $task['status'] === 'unassigned'))]) }}
            </div>
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-3 text-sm font-semibold text-indigo-900">
                <span x-text="@js(__('engineering_page.construction_count', ['count' => ':count'])).replace(':count', countStage('CONSTRUCTION'))">{{ __('engineering_page.construction_count', ['count' => count(array_filter($projects, fn($project) => $project['engineering_stage'] === 'CONSTRUCTION'))]) }}</span></div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="{{ __('engineering_page.survey_filters') }}">
            <button type="button" @click="queueFilter = 'all'" :aria-pressed="queueFilter === 'all'"
                :class="queueFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700'"
                class="min-h-11 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold">{{ __('engineering_page.all_projects') }}</button>
            <button type="button" @click="queueFilter = 'survey'" :aria-pressed="queueFilter === 'survey'"
                :class="queueFilter === 'survey' ? 'bg-cyan-700 text-white' : 'bg-white text-slate-700'"
                class="min-h-11 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold">{{ __('engineering_page.awaiting_survey') }}</button>
            <button type="button" @click="queueFilter = 'construction'" :aria-pressed="queueFilter === 'construction'"
                :class="queueFilter === 'construction' ? 'bg-indigo-700 text-white' : 'bg-white text-slate-700'"
                class="min-h-11 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold">{{ __('engineering_page.construction') }}</button>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-950">{{ __('engineering_page.project_list') }}</h2>
                <span class="text-sm text-slate-500">{{ __('engineering_page.project_count', ['count' => count($projects)]) }}</span>
            </div>
            @forelse($projects as $project)
                    <div x-show="queueFilter === 'all' || (queueFilter === 'survey' && (updatedStages[@js($project['lead_source_id'])] || @js($project['engineering_stage'])) === 'AWAITING_SURVEY') || (queueFilter === 'construction' && (updatedStages[@js($project['lead_source_id'])] || @js($project['engineering_stage'])) === 'CONSTRUCTION')"
                        class="grid grid-cols-2 gap-3 border-b border-slate-100 px-5 py-4 text-sm last:border-0 md:grid-cols-[2fr_1fr_1fr_1fr_auto] md:items-center">
                        <div class="col-span-2 min-w-0 md:col-span-1">
                            <p class="break-words font-semibold text-slate-950">{{ $project['site_name'] }}</p>
                            <p class="mt-1 break-all text-xs text-slate-500">{{ $project['project_code'] }}</p>
                        </div>
                        <div><span class="text-xs text-slate-500">{{ __('engineering_page.stage') }}</span>
                            <p
                                x-text="stageLabel(updatedStages[@js($project['lead_source_id'])] || @js($project['engineering_stage']))">{{ __('engineering_js.stages')[$project['engineering_stage']] ?? $project['engineering_stage'] }}</p>
                        </div>
                        <div><span class="text-xs text-slate-500">{{ __('engineering_page.origin') }}</span>
                            <p>{{ $project['origin'] === 'legacy' ? __('engineering_page.legacy') : __('engineering_page.current') }}</p>
                        </div>
                        <div class="col-span-2 min-w-0 md:col-span-1"><span class="text-xs text-slate-500">{{ __('engineering_page.connection') }}</span>
                            <p class="break-words">{{ $project['sync_status'] === 'pending_connection'
                    ? __('engineering_page.pending_connection')
                    : __('engineering_page.legacy_connection') }}</p>
                        </div>
                        <button type="button"
                            class="col-span-2 min-h-11 rounded-xl border border-cyan-200 px-3 py-2 font-semibold text-cyan-800 hover:bg-cyan-50 md:col-span-1"
                            @click="open(@js($project['lead_source_id']), $event.currentTarget)">{{ __('engineering_page.view_project') }}</button>
                    </div>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">{{ __('engineering_page.no_projects') }}</p>
            @endforelse
        </div>
        <div x-show="isOpen" x-cloak class="crm-overlay-layer fixed inset-0 z-50 flex justify-end" aria-hidden="false">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="close()"></div>
            <section x-ref="dialog" role="dialog" aria-modal="true" aria-labelledby="engineering-detail-title"
                class="relative flex h-full w-full max-w-2xl flex-col bg-slate-50 shadow-2xl">
                <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-4 py-5 sm:px-6">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-cyan-700">{{ __('engineering_page.title') }}</p>
                        <h2 id="engineering-detail-title" class="mt-1 text-xl font-bold text-slate-950"
                            x-text="project?.site_name || @js(__('engineering_page.project_detail'))"></h2>
                        <p class="mt-1 text-xs text-slate-500" x-text="project?.project_code || ''"></p>
                    </div>
                    <button type="button"
                        class="min-h-11 min-w-11 shrink-0 rounded-xl border border-slate-200 px-3 py-2 text-slate-600 hover:bg-slate-50"
                        @click="close()" aria-label="{{ __('my_work.close_details') }}">✕</button>
                </header>
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-6 sm:px-6">
                    <p x-show="loading" class="text-sm text-slate-500">{{ __('engineering_page.loading') }}</p>
                    <p x-show="error" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"
                        x-text="error"></p>
                    <template x-if="project && !loading">
                        <div class="space-y-5">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <dt class="text-xs text-slate-500">{{ __('engineering_page.stage') }}</dt>
                                    <dd class="mt-1 font-semibold text-slate-950"
                                        x-text="stageLabel(project.engineering_stage)"></dd>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <dt class="text-xs text-slate-500">{{ __('engineering_page.origin') }}</dt>
                                    <dd class="mt-1 font-semibold text-slate-950"
                                        x-text="project.origin === 'legacy' ? @js(__('engineering_page.legacy')) : @js(__('engineering_page.current'))"></dd>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:col-span-2">
                                    <dt class="text-xs text-slate-500">{{ __('engineering_page.connection') }}</dt>
                                    <dd class="mt-1 font-semibold text-slate-950" x-text="syncLabel(project)"></dd>
                                </div>
                            </div>
                            @if(auth()->user()->hasPermission('engineering.manage'))
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <label for="engineering-stage"
                                        class="block text-sm font-semibold text-slate-950">{{ __('engineering_page.change_stage') }}</label>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('engineering_page.stage_note') }}</p>
                                    <select id="engineering-stage" x-model="selectedStage" :disabled="saving"
                                        class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                        @foreach(__('engineering_js.stages') as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p x-show="saveError" role="alert" class="mt-3 text-sm text-rose-700" x-text="saveError">
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button type="button"
                                            class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                            :disabled="saving || selectedStage === project.engineering_stage || selectedStage === 'CONSTRUCTION'"
                                            @click="saveStage()"
                                            x-text="saving ? @js(__('engineering_page.saving')) : @js(__('engineering_page.save_stage'))"></button>
                                        <button x-show="conflict" type="button"
                                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700"
                                            @click="reloadLatest()">{{ __('engineering_page.reload') }}</button>
                                    </div>
                                    <p x-show="constructionStartHint()" class="mt-2 text-xs text-cyan-800"
                                        x-text="constructionStartHint()"></p>
                                </div>
                            @endif
                            <div x-show="surveyTask" class="rounded-2xl border border-cyan-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-widest text-cyan-700">Survey handoff
                                        </p>
                                        <h3 class="mt-1 text-base font-bold text-slate-950">{{ __('engineering_page.survey_work') }}</h3>
                                    </div><span
                                        class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-800"
                                        x-text="surveyStatusLabel(surveyTask?.status)"></span>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">{{ __('engineering_page.survey_note') }}</p>
                                <template x-if="surveyTask?.status === 'unassigned' || surveyTask?.status === 'scheduled'">
                                    <div class="mt-4 space-y-4">
                                        @if(auth()->user()->hasPermission('engineering.manage'))
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <label class="text-sm font-semibold text-slate-800">{{ __('engineering_page.owner') }}<select
                                                        x-model="surveyAssignee" :disabled="surveySaving"
                                                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                                        <option value="">{{ __('engineering_page.unassigned') }}</option>@foreach($surveyUsers as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                                                    </select></label>
                                                <label class="text-sm font-semibold text-slate-800">{{ __('engineering_page.survey_date') }}<input
                                                        type="datetime-local" x-model="surveyDate" :disabled="surveySaving"
                                                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"></label>
                                            </div>
                                            <div class="flex flex-wrap gap-2"><button type="button" @click="saveSurvey()"
                                                    :disabled="surveySaving || (!!surveyAssignee !== !!surveyDate)"
                                                    class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                                    x-text="surveySaving ? @js(__('engineering_page.saving')) : @js(__('engineering_page.save_assignment'))"></button>
                                            </div>
                                        @endif
                                        <div x-show="surveyTask?.status === 'scheduled' && (surveyTask?.assignee_user_id == @js(auth()->id()) || @js(auth()->user()->hasPermission('engineering.manage')))"
                                            class="border-t border-slate-100 pt-4"><label
                                                class="block text-sm font-semibold text-slate-800">{{ __('engineering_page.survey_result') }}<textarea
                                                    x-model="surveyResult" rows="3" maxlength="4000"
                                                    placeholder="{{ __('engineering_page.survey_result_hint') }}"
                                                    class="mt-1 block w-full rounded-xl border border-slate-200 p-3 text-sm"></textarea></label><button
                                                type="button" @click="completeSurvey()"
                                                :disabled="surveySaving || !surveyResult.trim()"
                                                class="mt-3 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{{ __('engineering_page.finish_survey') }}</button>
                                        </div>
                                    </div>
                                </template>
                                <p x-show="surveyTask?.status === 'done'"
                                    class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-900"
                                    x-text="surveyTask?.result_summary"></p>
                                <p x-show="surveyError" role="alert" class="mt-3 text-sm text-rose-700"
                                    x-text="surveyError"></p>
                                <button x-show="surveyError" type="button" @click="reloadLatest()"
                                    class="mt-2 text-sm font-semibold text-cyan-700">{{ __('engineering_page.reload') }}</button>
                            </div>
                            <div x-show="showConstructionDetails()"
                                class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-700">Construction
                                        </p>
                                        <h3 class="mt-1 text-base font-bold text-slate-950"
                                            x-text="project.engineering_stage === 'SURVEYED' ? @js(__('engineering_page.start_work')) : (project.engineering_stage === 'LIVE' ? @js(__('engineering_page.construction_summary')) : @js(__('engineering_page.follow_construction')))">
                                        </h3>
                                    </div><span
                                        class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-800"
                                        x-text="construction?.status === 'recorded' ? @js(__('engineering_page.has_progress')) : @js(__('engineering_page.no_progress'))"></span>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">{{ __('engineering_page.installation_status') }} <span
                                        class="font-semibold text-slate-700"
                                        x-text="construction?.installation_status || @js(__('engineering_page.unspecified'))"></span> ·
                                    {{ __('engineering_page.installation_note') }}</p>
                                <dl x-show="project.engineering_stage === 'LIVE'"
                                    class="mt-4 grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('engineering_page.owner') }}</dt>
                                        <dd class="font-semibold text-slate-900"
                                            x-text="construction?.owner_name || @js(__('engineering_page.unspecified'))"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('engineering_page.start_date') }}</dt>
                                        <dd class="font-semibold text-slate-900"
                                            x-text="construction?.started_on || @js(__('engineering_page.unspecified'))"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">{{ __('engineering_page.target_date') }}</dt>
                                        <dd class="font-semibold text-slate-900"
                                            x-text="construction?.target_finish_on || @js(__('engineering_page.unspecified'))"></dd>
                                    </div>
                                </dl>
                                @if(auth()->user()->hasPermission('engineering.manage'))
                                    <div x-show="canEditConstruction()" class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <label class="text-sm font-semibold text-slate-800">{{ __('engineering_page.owner') }}<select
                                                x-model="constructionOwner" :disabled="constructionSaving"
                                                class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                                <option value="">{{ __('engineering_page.select_owner') }}</option>@foreach($surveyUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                                            </select></label>
                                        <label class="text-sm font-semibold text-slate-800">{{ __('engineering_page.start_date') }}<input type="date"
                                                x-model="constructionStart" :disabled="constructionSaving"
                                                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></label>
                                        <label class="text-sm font-semibold text-slate-800 sm:col-span-2">{{ __('engineering_page.target_date_label') }}
                                            {{ __('engineering_page.optional') }}<input type="date" x-model="constructionTarget" :min="constructionStart"
                                                :disabled="constructionSaving"
                                                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></label>
                                    </div>
                                    <div x-show="project.engineering_stage === 'SURVEYED'" class="mt-4"><label
                                            class="block text-sm font-semibold text-slate-800">{{ __('engineering_page.start_note') }}
                                            {{ __('engineering_page.optional') }}<textarea x-model="constructionNote" maxlength="4000" rows="2"
                                                class="mt-1 block w-full rounded-xl border border-slate-200 p-3 text-sm"
                                                placeholder="{{ __('engineering_page.start_note_hint') }}"></textarea></label><button
                                            type="button" @click="startConstruction()"
                                            :disabled="constructionSaving || !constructionOwner || !constructionStart || (!!constructionTarget && constructionTarget < constructionStart)"
                                            class="mt-3 rounded-xl bg-indigo-700 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{{ __('engineering_page.start_construction') }}</button>
                                    </div>
                                @endif
                                <div x-show="project.engineering_stage === 'CONSTRUCTION'" class="mt-4 space-y-4">
                                    @if(auth()->user()->hasPermission('engineering.manage'))<button type="button"
                                        @click="saveConstruction()"
                                        :disabled="constructionSaving || !constructionOwner || !constructionStart || (!!constructionTarget && constructionTarget < constructionStart)"
                                    class="rounded-xl bg-indigo-700 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{{ __('engineering_page.save_owner_dates') }}</button>@endif
                                    <div x-show="construction?.status === 'recorded' && (construction?.owner_user_id == @js(auth()->id()) || @js(auth()->user()->hasPermission('engineering.manage')))"
                                        class="border-t border-slate-100 pt-4"><label
                                            class="block text-sm font-semibold text-slate-800">{{ __('engineering_page.add_progress') }}<textarea
                                                x-model="constructionNote" maxlength="4000" rows="3"
                                                class="mt-1 block w-full rounded-xl border border-slate-200 p-3 text-sm"
                                                placeholder="{{ __('engineering_page.progress_hint') }}"></textarea></label><button
                                            type="button" @click="addConstructionUpdate()"
                                            :disabled="constructionSaving || !constructionNote.trim()"
                                            class="mt-3 rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-800 disabled:opacity-50">{{ __('engineering_page.add_record') }}</button>
                                    </div>@if(auth()->user()->hasPermission('engineering.manage'))
                                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                            <p class="font-semibold text-emerald-950">{{ __('engineering_page.ready_live') }}</p>
                                            <p class="mt-1 text-sm text-emerald-900">
                                                {{ __('engineering_page.live_note') }}</p><button
                                                type="button" @click="selectedStage = 'LIVE'; saveStage()"
                                                :disabled="saving || constructionSaving"
                                                class="mt-3 w-full rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-50 sm:w-auto">{{ __('engineering_page.go_live') }}
                                                →</button>
                                    </div>@endif
                                </div>
                                <p x-show="constructionError" role="alert" class="mt-3 text-sm text-rose-700"
                                    x-text="constructionError"></p><button x-show="constructionError" type="button"
                                    @click="reloadLatest()"
                                    class="mt-2 text-sm font-semibold text-indigo-700">{{ __('engineering_page.reload') }}</button>
                                <div x-show="construction?.updates?.length" class="mt-5 border-t border-slate-100 pt-4">
                                    <h4 class="text-sm font-bold text-slate-900">{{ __('engineering_page.progress') }}</h4><template
                                        x-for="entry in construction?.updates || []" :key="entry.id">
                                        <div class="mt-3 rounded-xl bg-slate-50 p-3">
                                            <p class="whitespace-pre-wrap text-sm text-slate-800" x-text="entry.note"></p>
                                            <p class="mt-1 text-xs text-slate-500"
                                                x-text="`${entry.actor_name || @js(__('engineering_page.user'))} · ${entry.created_at || ''}`"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <h3 class="text-base font-bold text-slate-950">{{ __('engineering_page.stage_history') }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ __('engineering_page.history_note') }}</p>
                                <p x-show="!stageHistory.length" class="mt-4 text-sm text-slate-500">
                                    {{ __('engineering_page.no_history') }}</p>
                                <ol class="mt-4 space-y-3"><template x-for="entry in stageHistory" :key="entry.id">
                                        <li class="rounded-xl bg-slate-50 p-3 text-sm">
                                            <p class="font-semibold text-slate-900"
                                                x-text="`${stageLabel(entry.from_stage)} → ${stageLabel(entry.to_stage)}`">
                                            </p>
                                            <p class="mt-1 break-words text-xs text-slate-500"
                                                x-text="`${entry.actor_name || @js(__('engineering_page.user'))} · ${entry.created_at || ''}`"></p>
                                        </li>
                                    </template></ol>
                            </div>
                            <a :href="project ? `{{ route('crm.admin.pipeline.index') }}?open=${encodeURIComponent(project.lead_source_id)}` : '#'"
                                class="inline-flex text-sm font-semibold text-cyan-700 hover:underline">{{ __('engineering_page.related_sale') }}
                                ↗</a>
                        </div>
                    </template>
                </div>
            </section>
        </div>
        <div x-show="liveConfirmationOpen" x-cloak
            class="crm-overlay-layer fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            @click.self="cancelGoLive()">
            <section role="dialog" aria-modal="true" aria-labelledby="go-live-title"
                class="max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="bg-gradient-to-br from-emerald-800 to-cyan-800 px-6 py-5 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">GSB EV · Engineering</p>
                    <h2 id="go-live-title" class="mt-2 text-xl font-bold">{{ __('engineering_page.confirm_live') }}</h2>
                    <p class="mt-1 break-words text-sm text-emerald-50" x-text="project?.site_name || ''"></p>
                </div>
                <div class="space-y-4 p-6 text-sm text-slate-700">
                    <p>{{ __('engineering_page.confirm_note') }}</p>
                    <dl class="grid gap-3 rounded-2xl bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('engineering_page.owner') }}</dt>
                            <dd class="font-semibold" x-text="construction?.owner_name || @js(__('engineering_page.unspecified'))"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('engineering_page.start_date') }}</dt>
                            <dd class="font-semibold" x-text="construction?.started_on || @js(__('engineering_page.unspecified'))"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('engineering_page.target_date') }}</dt>
                            <dd class="font-semibold" x-text="construction?.target_finish_on || @js(__('engineering_page.unspecified'))"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('engineering_page.add_progress') }}</dt>
                            <dd class="font-semibold" x-text="@js(__('engineering_page.progress_count', ['count' => ':count'])).replace(':count', construction?.updates?.length || 0)"></dd>
                        </div>
                    </dl>
                    <p x-show="saveError" role="alert" class="rounded-xl bg-rose-50 p-3 text-rose-700" x-text="saveError">
                    </p>
                    <button x-show="conflict" type="button" @click="reloadLatest()"
                        class="font-semibold text-cyan-700 underline">{{ __('engineering_page.reload') }}</button>
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button"
                            @click="cancelGoLive()" :disabled="saving"
                            class="min-h-11 rounded-xl border border-slate-200 px-4 py-2.5 font-semibold disabled:opacity-50">{{ __('engineering_page.cancel') }}</button><button
                            x-ref="liveConfirm" type="button" @click="confirmGoLive()" :disabled="saving"
                            class="min-h-11 rounded-xl bg-emerald-700 px-5 py-2.5 font-bold text-white hover:bg-emerald-800 disabled:opacity-50"
                            x-text="saving ? @js(__('engineering_page.saving')) : @js(__('engineering_page.confirm_live'))"></button></div>
                </div>
            </section>
        </div>
    </div>
@endsection
