@extends('crm.layouts.admin')

@section('content')
<div class="space-y-6">
    <header class="crm-page-intro">
        <p class="crm-eyebrow">{{ __('crm_headings.settings') }}</p>
        <h1>{{ __('settings.title') }}</h1>
        <p>{{ __('settings.description') }}</p>
    </header>

    @if(session('status'))
        <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong>{{ __('settings.cannot_save') }}</strong>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="create-user-title">
        <div class="mb-5">
            <h2 id="create-user-title" class="text-lg font-bold text-slate-950">{{ __('settings.add_account') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('settings.add_note') }}</p>
        </div>
        <form method="POST" action="{{ route('crm.admin.settings.users.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <label><span class="crm-field-label">{{ __('settings.name') }} *</span><input name="name" value="{{ old('name') }}" required maxlength="255" class="crm-site-input"></label>
            <label><span class="crm-field-label">{{ __('settings.email') }} *</span><input name="email" type="email" value="{{ old('email') }}" required maxlength="255" class="crm-site-input"></label>
            <label><span class="crm-field-label">{{ __('settings.role') }} *</span><select name="role_id" required class="crm-site-input"><option value="">{{ __('settings.select_role') }}</option>@foreach($assignableRoles as $role)<option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>@endforeach</select></label>
            <label><span class="crm-field-label">{{ __('settings.password') }} *</span><input name="password" type="password" required autocomplete="new-password" class="crm-site-input"></label>
            <label><span class="crm-field-label">{{ __('settings.confirm_password') }} *</span><input name="password_confirmation" type="password" required autocomplete="new-password" class="crm-site-input"></label>
            <div class="md:col-span-2 xl:col-span-5"><button class="rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:from-teal-700 hover:to-cyan-700">{{ __('settings.add') }}</button></div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="users-title">
        <div class="border-b border-slate-200 p-5"><h2 id="users-title" class="text-lg font-bold text-slate-950">{{ __('settings.users') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('settings.users_note') }}</p></div>
        <div class="divide-y divide-slate-100">
            @foreach($users as $user)
                @if($user->role?->slug === 'super_admin')
                    <article class="flex flex-wrap items-center justify-between gap-4 bg-cyan-50/50 p-5">
                        <div><p class="font-bold text-slate-950">{{ $user->name }}</p><p class="mt-1 text-sm text-slate-600">{{ $user->email }}</p></div>
                        <div class="text-right"><span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold text-white">Super Admin</span><p class="mt-2 text-xs text-slate-500">{{ __('settings.primary_account') }}</p></div>
                    </article>
                @else
                    <form method="POST" action="{{ route('crm.admin.settings.users.update', $user) }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-[1fr_1.2fr_12rem_1fr_1fr_auto] xl:items-end">
                        @csrf @method('PATCH')
                        <label><span class="crm-field-label">{{ __('settings.name') }}</span><input name="name" value="{{ $user->name }}" required maxlength="255" class="crm-site-input"></label>
                        <label><span class="crm-field-label">{{ __('settings.email') }}</span><input name="email" type="email" value="{{ $user->email }}" required maxlength="255" class="crm-site-input"></label>
                        <label><span class="crm-field-label">{{ __('settings.role') }}</span><select name="role_id" required class="crm-site-input">@foreach($assignableRoles as $role)<option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>@endforeach</select></label>
                        <label><span class="crm-field-label">{{ __('settings.new_password') }}</span><input name="password" type="password" autocomplete="new-password" placeholder="{{ __('settings.new_password_hint') }}" class="crm-site-input"></label>
                        <label><span class="crm-field-label">{{ __('settings.confirm_new_password') }}</span><input name="password_confirmation" type="password" autocomplete="new-password" class="crm-site-input"></label>
                        <button class="rounded-xl border border-cyan-200 bg-white px-4 py-2.5 text-sm font-semibold text-cyan-800 hover:bg-cyan-50">{{ __('settings.save') }}</button>
                    </form>
                @endif
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="permissions-title">
        <div class="border-b border-slate-200 p-5"><h2 id="permissions-title" class="text-lg font-bold text-slate-950">{{ __('settings.permissions') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('settings.permissions_note') }}</p></div>
        <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach($roles as $role)
                <article class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-3"><h3 class="font-bold text-slate-950">{{ $role->name }}</h3><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">{{ $role->slug }}</span></div>
                    @if($role->slug === 'super_admin')
                        <p class="mt-4 rounded-xl bg-cyan-50 p-3 text-sm font-semibold text-cyan-900">{{ __('settings.all_permissions') }}</p>
                    @else
                        <ul class="mt-4 space-y-2 text-sm text-slate-700">
                            @foreach($role->permissions->sortBy('permission') as $permission)
                                <li class="flex gap-2"><span class="text-emerald-600" aria-hidden="true">✓</span><span>{{ $permissionLabels[$permission->permission] ?? $permission->permission }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
