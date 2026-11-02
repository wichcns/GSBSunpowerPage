<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        return User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', $slug)->value('id')]);
    }

    public function test_roles_have_english_names_and_missing_role_has_no_permissions(): void
    {
        $this->assertSame(['Super Admin', 'Admin', 'Manager', 'Staff'],
            DB::table('crm_roles')->orderBy('id')->pluck('name')->all());
        $unassigned = User::factory()->create(['role_id' => null]);
        $this->assertFalse($unassigned->hasPermission('dashboard.view'));
        $this->actingAs($unassigned)->get('/crm/admin')->assertForbidden();
    }

    public function test_super_admin_has_all_and_manager_and_staff_have_limited_capabilities(): void
    {
        $super = $this->userWithRole('super_admin');
        $manager = $this->userWithRole('manager');
        $staff = $this->userWithRole('staff');

        $this->assertTrue($super->hasPermission('system.manage'));
        $this->assertTrue($super->hasPermission('ownership.manage'));
        $this->assertTrue($manager->hasPermission('ownership.manage'));
        $this->assertFalse($manager->hasPermission('system.manage'));
        $this->assertTrue($staff->hasPermission('work.view'));
        $this->assertFalse($staff->hasPermission('ownership.manage'));
        $this->assertFalse($staff->hasPermission('pipeline.stage'));
    }

    public function test_staff_cannot_call_the_sales_stage_update_route(): void
    {
        $staff = $this->userWithRole('staff');

        $this->actingAs($staff)->patchJson('/crm/admin/pipeline/any-lead/stage', [
            'stage' => 'INTERNAL_REVIEW',
            'expected_version' => 'legacy:1:test',
        ])->assertForbidden();
    }

    public function test_menu_and_direct_routes_obey_role_permissions(): void
    {
        $manager = $this->userWithRole('manager');
        $staff = $this->userWithRole('staff');

        $this->actingAs($manager)->get('/crm/admin')->assertOk()
            ->assertSee('ยืนยันผู้รับผิดชอบ')->assertSee('สัญญา')->assertDontSee('ตั้งค่าระบบ');
        $this->get('/crm/admin/ownership')->assertOk();
        $this->get('/crm/admin/settings')->assertForbidden();

        $this->actingAs($staff)->get('/crm/admin')->assertOk()
            ->assertSee('งานของฉัน')->assertDontSee('ยืนยันผู้รับผิดชอบ')
            ->assertDontSee('href="http://localhost/crm/admin/contracts"', false)->assertDontSee('ตั้งค่าระบบ');
        $this->get('/crm/admin/ownership')->assertForbidden();
        $this->post('/crm/admin/ownership/assign', [])->assertForbidden();
        $this->get('/crm/admin/contracts')->assertForbidden();
        $this->get('/crm/admin/my-work')->assertOk();
        $this->get('/crm/admin/pipeline')->assertOk()->assertDontSee('บันทึกขั้นตอน');
        $this->get('/crm/admin/engineering')->assertOk()->assertDontSee('@click="saveSurvey()"', false);
    }

    public function test_sidebar_places_shared_links_first_without_group_headings(): void
    {
        $response = $this->actingAs($this->userWithRole('manager'))->get('/crm/admin')->assertOk();
        $html = $response->getContent();

        $this->assertStringNotContainsString('เมนูทั่วไป', $html);
        $this->assertStringNotContainsString('เมนูตามสิทธิ์', $html);
        $this->assertLessThan(strpos($html, route('crm.admin.pipeline.index')), strpos($html, route('crm.admin.my-work')));
        $this->assertStringNotContainsString('ตั้งค่าระบบ', $html);

        $staffHtml = $this->actingAs($this->userWithRole('staff'))->get('/crm/admin')->assertOk()->getContent();
        $this->assertStringNotContainsString('เมนูทั่วไป', $staffHtml);
        $this->assertStringContainsString('href="'.route('crm.admin.pipeline.index').'"', $staffHtml);
        $this->assertStringContainsString('href="'.route('crm.admin.engineering.index').'"', $staffHtml);
        $this->assertStringNotContainsString('ตั้งค่าระบบ', $staffHtml);

        $superHtml = $this->actingAs($this->userWithRole('super_admin'))->get('/crm/admin')->assertOk()->getContent();
        $this->assertStringNotContainsString('เมนูตามสิทธิ์', $superHtml);
        $this->assertStringContainsString('ตั้งค่าระบบ', $superHtml);
    }

    public function test_staff_cannot_open_unassigned_pipeline_or_engineering_records(): void
    {
        $staff = $this->userWithRole('staff');
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'role-test', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (['mine', 'other'] as $sourceId) {
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => json_encode(['site_name' => $sourceId, 'stage' => 'NEW']),
                'payload_sha256' => str_repeat('b', 64), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('crm_engineering_projects')->insert([
                'lead_source_id' => $sourceId, 'project_code' => $sourceId, 'engineering_stage' => 'AWAITING_SURVEY',
                'sync_status' => 'pending_connection', 'origin' => 'legacy', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('crm_sites')->insert([
            'site_code' => 'ROLE-1', 'name' => 'mine', 'legacy_lead_id' => 'mine',
            'owner_user_id' => $staff->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_survey_tasks')->insert([
            'engineering_project_id' => DB::table('crm_engineering_projects')->where('lead_source_id', 'mine')->value('id'),
            'lead_source_id' => 'mine', 'assignee_user_id' => $staff->id, 'status' => 'scheduled',
            'scheduled_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($staff)->get('/crm/admin/pipeline')->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->items()[0]['source_id'] === 'mine');
        $this->get('/crm/admin/pipeline/mine')->assertOk();
        $this->get('/crm/admin/pipeline/other')->assertForbidden();
        $this->post('/crm/admin/pipeline/other/visits', [])->assertForbidden();
        $this->get('/crm/admin/engineering')->assertOk()->assertSee('mine')->assertDontSee('other');
        $this->get('/crm/admin/engineering/mine')->assertOk();
        $this->get('/crm/admin/engineering/other')->assertForbidden();
        $this->post('/crm/admin/engineering/other/survey/complete', [])->assertForbidden();
        $this->post('/crm/admin/engineering/mine/construction/updates', [])->assertForbidden();
    }
}
