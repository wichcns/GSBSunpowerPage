<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug, array $attributes = []): User
    {
        return User::factory()->create(array_merge($attributes, [
            'role_id' => DB::table('crm_roles')->where('slug', $slug)->value('id'),
        ]));
    }

    public function test_only_super_admin_can_open_and_submit_user_management(): void
    {
        $super = $this->userWithRole('super_admin');
        $admin = $this->userWithRole('admin');

        $this->actingAs($super)->get('/crm/admin/settings')->assertOk()
            ->assertSee('จัดการผู้ใช้และสิทธิ์')
            ->assertSee('เพิ่มบัญชี CRM')
            ->assertSee('ตารางสิทธิ์ตาม Role');

        $this->actingAs($admin)->get('/crm/admin/settings')->assertForbidden();
        $this->post('/crm/admin/settings/users', [])->assertForbidden();
        $this->patch('/crm/admin/settings/users/'.$super->id, [])->assertForbidden();
    }

    public function test_super_admin_can_create_a_user_with_an_assignable_role(): void
    {
        $super = $this->userWithRole('super_admin');
        $managerRole = DB::table('crm_roles')->where('slug', 'manager')->first();

        $this->actingAs($super)->post('/crm/admin/settings/users', [
            'name' => 'Manager New',
            'email' => '  MANAGER.NEW@EXAMPLE.COM ',
            'role_id' => $managerRole->id,
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect('/crm/admin/settings');

        $created = User::query()->where('email', 'manager.new@example.com')->firstOrFail();
        $this->assertSame('Manager New', $created->name);
        $this->assertSame($managerRole->id, $created->role_id);
        $this->assertTrue(Hash::check('safe-password-123', $created->password));
    }

    public function test_create_rejects_duplicate_email_and_super_admin_role(): void
    {
        $super = $this->userWithRole('super_admin');
        $this->userWithRole('staff', ['email' => 'existing@example.com']);

        $this->actingAs($super)->from('/crm/admin/settings')->post('/crm/admin/settings/users', [
            'name' => 'Duplicate',
            'email' => 'EXISTING@EXAMPLE.COM',
            'role_id' => DB::table('crm_roles')->where('slug', 'super_admin')->value('id'),
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect('/crm/admin/settings')->assertSessionHasErrors(['email', 'role_id']);

        $this->assertSame(2, User::query()->count());
    }

    public function test_super_admin_can_update_standard_user_and_blank_password_is_preserved(): void
    {
        $super = $this->userWithRole('super_admin');
        $staff = $this->userWithRole('staff', [
            'name' => 'Staff Before',
            'email' => 'staff.before@example.com',
            'password' => 'original-password',
        ]);
        $originalPassword = $staff->password;
        $adminRoleId = DB::table('crm_roles')->where('slug', 'admin')->value('id');

        $this->actingAs($super)->patch('/crm/admin/settings/users/'.$staff->id, [
            'name' => 'Staff After',
            'email' => 'STAFF.AFTER@EXAMPLE.COM',
            'role_id' => $adminRoleId,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/crm/admin/settings');

        $staff->refresh();
        $this->assertSame('Staff After', $staff->name);
        $this->assertSame('staff.after@example.com', $staff->email);
        $this->assertSame($adminRoleId, $staff->role_id);
        $this->assertSame($originalPassword, $staff->password);
    }

    public function test_super_admin_account_cannot_be_changed_and_invalid_update_is_atomic(): void
    {
        $super = $this->userWithRole('super_admin', ['name' => 'Root', 'email' => 'root@example.com']);
        $staff = $this->userWithRole('staff', ['name' => 'Staff', 'email' => 'staff@example.com']);

        $this->actingAs($super)->from('/crm/admin/settings')->patch('/crm/admin/settings/users/'.$super->id, [
            'name' => 'Changed Root',
            'email' => 'changed.root@example.com',
            'role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id'),
        ])->assertForbidden();

        $this->actingAs($super)->from('/crm/admin/settings')->patch('/crm/admin/settings/users/'.$staff->id, [
            'name' => 'Changed Staff',
            'email' => 'root@example.com',
            'role_id' => DB::table('crm_roles')->where('slug', 'manager')->value('id'),
        ])->assertRedirect('/crm/admin/settings')->assertSessionHasErrors('email');

        $this->assertSame('Root', $super->fresh()->name);
        $this->assertSame('Staff', $staff->fresh()->name);
        $this->assertSame('staff@example.com', $staff->fresh()->email);
    }

    public function test_settings_page_marks_super_admin_as_protected_and_lists_effective_permissions(): void
    {
        $super = $this->userWithRole('super_admin', ['name' => 'System Owner']);
        $this->userWithRole('staff', ['name' => 'Field Staff']);

        $this->actingAs($super)->get('/crm/admin/settings')->assertOk()
            ->assertSee('System Owner')
            ->assertSee('บัญชีหลักของระบบ')
            ->assertSee('Field Staff')
            ->assertSee('Super Admin')
            ->assertSee('Admin')
            ->assertSee('Manager')
            ->assertSee('Staff')
            ->assertSee('งานของฉัน')
            ->assertSee('จัดการระบบ');
    }
}
