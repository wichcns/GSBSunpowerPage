<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_sees_the_desktop_navigation_and_mobile_toggle(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('crm.admin.dashboard'))
            ->assertOk()
            ->assertSee('GSB EV')
            ->assertSee('/crm-assets/gsb-ev-logo.png')
            ->assertSee('ภาพรวมงาน')
            ->assertSee('Toggle navigation');
    }

    public function test_sidebar_uses_the_authenticated_users_language(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->get(route('crm.admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('My work')
            ->assertSee('Sites and customers');

        $user->update(['locale' => 'zh']);
        $this->get(route('crm.admin.dashboard'))
            ->assertOk()
            ->assertSee('工作总览')
            ->assertSee('我的工作')
            ->assertSee('站点与客户');
    }
}
