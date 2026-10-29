<?php

namespace Tests\Feature;

use App\Crm\Models\User as CrmUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmRouteIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_login_and_public_admin_login_have_separate_routes(): void
    {
        $this->get('/crm/login')->assertOk();
        $this->get('/admin/login')->assertOk();
        $this->get('/')->assertOk();

        $this->assertSame('/crm/login', route('crm.login', absolute: false));
        $this->assertSame('/admin/login', route('admin.login', absolute: false));
    }

    public function test_crm_dashboard_requires_crm_authentication(): void
    {
        $this->get('/crm/admin')->assertRedirect('/crm/login');
    }

    public function test_crm_login_uses_only_crm_users(): void
    {
        DB::table('crm_users')->insert([
            'name' => 'CRM User',
            'email' => 'crm@example.test',
            'password' => Hash::make('test-password'),
        ]);

        $this->post('/crm/login', ['email' => 'crm@example.test', 'password' => 'test-password'])
            ->assertRedirect('/crm/admin');
        $this->assertAuthenticated('crm');
        $this->assertGuest('web');
    }

    public function test_crm_super_admin_can_open_dashboard_without_entering_public_admin(): void
    {
        $roleId = DB::table('crm_roles')->where('slug', 'super_admin')->value('id');
        $user = CrmUser::query()->create([
            'name' => 'CRM Owner',
            'email' => 'owner@example.test',
            'password' => Hash::make('test-password'),
            'role_id' => $roleId,
        ]);

        $this->actingAs($user, 'crm')->get('/crm/admin')->assertOk();
        $this->get('/crm/admin/sites')->assertOk();
        $this->get('/crm/admin/pipeline')->assertOk();
        $this->get('/crm/admin/contracts')->assertOk();
        $this->get('/crm/admin/engineering')->assertOk();
        $this->get('/crm/admin/reports')->assertOk();
        $this->get('/crm/admin/reports/export')->assertOk();
        $this->assertGuest('web');
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
