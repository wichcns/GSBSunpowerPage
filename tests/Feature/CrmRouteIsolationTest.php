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

    public function test_crm_entry_stays_in_crm_for_authenticated_user(): void
    {
        $this->get('/crm')->assertRedirect('/crm/login');

        $roleId = DB::table('crm_roles')->where('slug', 'super_admin')->value('id');
        $user = CrmUser::query()->create([
            'name' => 'CRM User',
            'email' => 'entry@example.test',
            'password' => Hash::make('test-password'),
            'role_id' => $roleId,
        ]);

        $this->actingAs($user, 'crm');
        $this->get('/crm')->assertRedirect('/crm/admin');
        $this->get('/crm/login')->assertRedirect('/crm/admin');
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

    public function test_crm_session_cookie_ends_with_browser_and_is_scoped_to_crm(): void
    {
        $response = $this->get('/crm/login')->assertOk();
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'crm_browser_session');

        $this->assertNotNull($cookie);
        $this->assertSame('/crm', $cookie->getPath());
        $this->assertSame(0, $cookie->getExpiresTime());

        $publicResponse = $this->get('/admin/login')->assertOk();
        $publicCookie = collect($publicResponse->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertNotNull($publicCookie);
        $this->assertNotSame('crm_browser_session', $publicCookie->getName());
        $this->assertGreaterThan(time(), $publicCookie->getExpiresTime());
    }

    public function test_crm_ignores_stale_remember_me_submission(): void
    {
        DB::table('crm_users')->insert([
            'name' => 'CRM User',
            'email' => 'remember@example.test',
            'password' => Hash::make('test-password'),
        ]);

        $response = $this->post('/crm/login', [
            'email' => 'remember@example.test',
            'password' => 'test-password',
            'remember' => 'on',
        ])->assertRedirect('/crm/admin');

        $this->assertNull(DB::table('crm_users')->where('email', 'remember@example.test')->value('remember_token'));
        $this->assertFalse(collect($response->headers->getCookies())->contains(
            fn ($cookie) => str_starts_with($cookie->getName(), 'remember_')
        ));
    }

    public function test_crm_login_does_not_offer_remember_me(): void
    {
        $this->get('/crm/login')->assertOk()->assertDontSee('name="remember"', false);
    }
}
