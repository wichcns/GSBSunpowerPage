<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Models\Role;
use App\Crm\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->with('permissions')->orderBy('id')->get();

        return view('crm.admin.settings.index', [
            'users' => User::query()->with('role')->orderBy('name')->orderBy('id')->get(),
            'roles' => $roles,
            'assignableRoles' => $roles->where('slug', '!=', 'super_admin')->values(),
            'permissionLabels' => $this->permissionLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('crm_users', 'email')],
            'role_id' => ['required', 'integer', $this->assignableRoleRule()],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::query()->create($data);

        return redirect()->route('crm.admin.settings.index')->with('status', __('settings.created'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role?->slug === 'super_admin', 403);

        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('crm_users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'integer', $this->assignableRoleRule()],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('crm.admin.settings.index')->with('status', __('settings.updated'));
    }

    private function assignableRoleRule(): Exists
    {
        return Rule::exists('crm_roles', 'id')->where(fn ($query) => $query->where('slug', '!=', 'super_admin'));
    }

    /** @return array<string, string> */
    private function permissionLabels(): array
    {
        return __('settings.permission_labels');
    }
}
