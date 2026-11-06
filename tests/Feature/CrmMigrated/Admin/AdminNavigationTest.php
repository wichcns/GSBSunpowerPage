<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('adminRoutes')]
    public function test_an_admin_can_open_every_approved_module(string $route): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route($route))
            ->assertOk();
    }

    public static function adminRoutes(): array
    {
        return [
            ['crm.admin.dashboard'], ['crm.admin.my-work'], ['crm.admin.team-work'],
            ['crm.admin.ownership.index'], ['crm.admin.notifications.index'], ['crm.admin.manuals.index'],
            ['crm.admin.pipeline.index'], ['crm.admin.contracts.index'], ['crm.admin.engineering.index'],
            ['crm.admin.activities.index'], ['crm.admin.reports.index'], ['crm.admin.integrations.index'],
            ['crm.admin.settings.index'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_every_admin_module_uses_the_selected_english_locale(string $route): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        $response = $this->actingAs($user)->get(route($route))->assertOk();
        $systemContent = preg_replace('/<option value="th"[^>]*>ไทย<\/option>/', '', $response->getContent());

        $this->assertDoesNotMatchRegularExpression('/[ก-๙]/u', $systemContent, "Thai system text remains on route {$route}");
    }
}
