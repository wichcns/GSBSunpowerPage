<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\ReadModels\DashboardOverview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_summary_is_scoped_to_the_account_and_reflects_new_crm_edits(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-26 12:00:00'));
        $first = $this->account('staff', 'Staff One');
        $second = $this->account('staff', 'Staff Two');
        $this->leadBatch([
            'mine' => ['site_name' => 'Mine', 'stage' => 'NEGOTIATING', 'next_action' => 'Call mine', 'next_action_at' => '2026-10-25 10:00:00'],
            'other' => ['site_name' => 'Other', 'stage' => 'NEGOTIATING', 'next_action' => 'Call other', 'next_action_at' => null],
        ]);
        Site::create(['name' => 'Mine', 'legacy_lead_id' => 'mine', 'owner_user_id' => $first->id]);
        Site::create(['name' => 'Other', 'legacy_lead_id' => 'other', 'owner_user_id' => $second->id]);

        $overview = app(DashboardOverview::class)->forUser($first);
        $this->assertSame('personal', $overview['scope']);
        $this->assertSame(1, $overview['cards']['sites']['value']);
        $this->assertSame(1, $overview['cards']['tasks']['value']);
        $this->assertSame(1, $overview['cards']['overdue']['value']);
        $this->assertSame(0, $overview['cards']['undated']['value']);
        $this->assertSame('Mine', $overview['attention'][0]['site_name']);
        $this->assertStringNotContainsString('Other', json_encode($overview, JSON_THROW_ON_ERROR));
        $this->assertSame(route('crm.admin.my-work'), $overview['primary_url']);
        $this->assertNotNull($overview['updated_at']);
        $otherOverview = app(DashboardOverview::class)->forUser($second);
        $this->assertSame(1, $otherOverview['cards']['undated']['value']);
        $this->assertSame([], $otherOverview['attention']);

        $response = $this->actingAs($first)->get('/crm/admin?role=super_admin&user_id='.$second->id)->assertOk();
        $response->assertViewHas('overview', fn (array $payload) => $payload['scope'] === 'personal'
            && $payload['cards']['tasks']['value'] === 1
            && !str_contains(json_encode($payload, JSON_THROW_ON_ERROR), 'Other'));

        DB::table('crm_lead_details')->insert([
            'lead_source_id' => 'mine', 'profile' => json_encode(['next_action' => null, 'next_action_at' => null]),
            'technical' => '{}', 'updated_by_user_id' => $first->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->assertSame(0, app(DashboardOverview::class)->forUser($first)['cards']['tasks']['value']);
    }

    public function test_manager_sees_all_staff_assignments_but_no_admin_owned_site(): void
    {
        $manager = $this->account('manager', 'Manager');
        $first = $this->account('staff', 'Alpha');
        $second = $this->account('staff', 'Beta');
        $admin = $this->account('admin', 'Admin');
        $this->leadBatch([
            'alpha' => ['site_name' => 'Alpha Site', 'stage' => 'NEGOTIATING', 'next_action' => 'Call Alpha', 'next_action_at' => '2020-01-01 10:00:00'],
            'beta' => ['site_name' => 'Beta Site', 'stage' => 'NEGOTIATING', 'next_action' => 'Call Beta', 'next_action_at' => null],
            'admin' => ['site_name' => 'Admin Site', 'stage' => 'NEGOTIATING', 'next_action' => 'Admin action', 'next_action_at' => null],
        ]);
        Site::create(['name' => 'Alpha Site', 'legacy_lead_id' => 'alpha', 'owner_user_id' => $first->id]);
        Site::create(['name' => 'Beta Site', 'legacy_lead_id' => 'beta', 'owner_user_id' => $second->id]);
        Site::create(['name' => 'Admin Site', 'legacy_lead_id' => 'admin', 'owner_user_id' => $admin->id]);

        $overview = app(DashboardOverview::class)->forUser($manager);
        $this->assertSame('staff_all', $overview['scope']);
        $this->assertSame(2, $overview['cards']['staff']['value']);
        $this->assertSame(2, $overview['cards']['sites']['value']);
        $this->assertSame(2, $overview['cards']['tasks']['value']);
        $this->assertSame(1, $overview['cards']['overdue']['value']);
        $this->assertSame('Alpha', $overview['attention'][0]['title']);
        $this->assertSame(route('crm.admin.team-work', ['staff' => $first->id]), $overview['attention'][0]['url']);
        $this->assertStringNotContainsString('Admin Site', json_encode($overview, JSON_THROW_ON_ERROR));
    }

    public function test_admin_and_super_admin_see_current_organization_counts_and_contract_attention(): void
    {
        $staff = $this->account('staff', 'Staff');
        $admin = $this->account('admin', 'Admin');
        $super = $this->account('super_admin', 'Super');
        $this->leadBatch([
            'contract' => ['site_name' => 'Contract Site', 'stage' => 'CONTRACT'],
            'project' => ['site_name' => 'Project Site', 'stage' => 'SIGNED'],
        ]);
        Site::create(['name' => 'Contract Site', 'legacy_lead_id' => 'contract', 'owner_user_id' => $staff->id]);
        Site::create(['name' => 'Project Site', 'legacy_lead_id' => 'project']);
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'project', 'project_code' => 'GSB-TEST', 'engineering_stage' => 'AWAITING_SURVEY',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ([$admin, $super] as $user) {
            $overview = app(DashboardOverview::class)->forUser($user);
            $this->assertSame('organization', $overview['scope']);
            $this->assertSame(2, $overview['cards']['sites']['value']);
            $this->assertSame(2, $overview['cards']['opportunities']['value']);
            $this->assertSame(2, $overview['cards']['contracts']['value']);
            $this->assertSame(1, $overview['cards']['engineering']['value']);
            $this->assertSame('Contract Site', $overview['attention'][0]['title']);
            $this->assertSame(route('crm.admin.contracts.index', ['information' => 'missing']), $overview['attention'][0]['url']);
        }
    }

    public function test_empty_dashboard_has_zero_counts_and_no_fake_attention(): void
    {
        foreach (['manager' => 'staff_all', 'admin' => 'organization', 'staff' => 'personal'] as $role => $scope) {
            $user = $this->account($role, $role);
            $overview = app(DashboardOverview::class)->forUser($user);
            $this->assertSame($scope, $overview['scope']);
            $this->assertNull($overview['updated_at']);
            $this->assertSame([], $overview['attention']);
            foreach ($overview['cards'] as $card) $this->assertSame(0, $card['value']);
        }
        $this->actingAs($this->account('admin', 'Empty Charts'))->get('/crm/admin')->assertOk()
            ->assertSee('ยังไม่มีรายการสำหรับแสดงกราฟ');
    }

    public function test_dashboard_ui_shows_role_specific_cards_and_allowed_shortcuts(): void
    {
        $staff = $this->account('staff', 'Staff UI');
        $manager = $this->account('manager', 'Manager UI');
        $admin = $this->account('admin', 'Admin UI');
        $this->leadBatch([
            'assigned' => ['site_name' => 'Assigned Site', 'stage' => 'NEGOTIATING', 'next_action' => 'Call assigned', 'next_action_at' => '2020-01-01 10:00:00'],
        ]);
        Site::create(['name' => 'Assigned Site', 'legacy_lead_id' => 'assigned', 'owner_user_id' => $staff->id]);

        $staffPage = $this->actingAs($staff)->get('/crm/admin')->assertOk();
        $staffPage->assertSee('สถานที่ที่รับผิดชอบ')->assertSee('งานเกินกำหนด')
            ->assertSee('ดูงานของฉัน')->assertSee('Assigned Site')
            ->assertDontSee('รายการที่เกี่ยวข้องกับสัญญา')
            ->assertDontSee('Staff ทุกคนใน CRM')
            ->assertDontSee('href="'.route('crm.admin.team-work').'"', false);

        $managerPage = $this->actingAs($manager)->get('/crm/admin')->assertOk();
        $managerPage->assertSee('Staff ทุกคนใน CRM')->assertSee('งานของ Staff')
            ->assertSee('ดูงานของทีม')
            ->assertSee('href="'.route('crm.admin.team-work', ['staff' => $staff->id]).'"', false)
            ->assertDontSee('ดูงานของฉัน');

        $adminPage = $this->actingAs($admin)->get('/crm/admin')->assertOk();
        $adminPage->assertSee('โอกาสขาย')->assertSee('รายการที่เกี่ยวข้องกับสัญญา')
            ->assertSee('โครงการวิศวกรรม')
            ->assertSee('href="'.route('crm.admin.sites.index').'"', false)
            ->assertSee('href="'.route('crm.admin.pipeline.index').'"', false)
            ->assertSee('href="'.route('crm.admin.contracts.index').'"', false)
            ->assertSee('href="'.route('crm.admin.engineering.index').'"', false)
            ->assertDontSee('รอข้อมูล CRM ปัจจุบัน');
    }

    public function test_completed_empty_import_shows_real_zero_state_not_pending_data(): void
    {
        $this->leadBatch([]);
        $this->actingAs($this->account('admin', 'Admin Zero'))->get('/crm/admin')->assertOk()
            ->assertSee('ยังไม่มีรายการที่ต้องติดตาม')
            ->assertDontSee('ยังไม่มีข้อมูล CRM')
            ->assertDontSee('รอข้อมูล CRM ปัจจุบัน');
    }

    public function test_dashboard_does_not_expose_modules_removed_from_admin_permissions(): void
    {
        $admin = $this->account('admin', 'Restricted Admin');
        DB::table('crm_role_permissions')->where('role_id', $admin->role_id)
            ->whereIn('permission', ['pipeline.view', 'contracts.view', 'engineering.view'])
            ->delete();
        $admin->unsetRelation('role');
        $this->leadBatch(['hidden' => ['site_name' => 'Hidden Contract', 'stage' => 'CONTRACT']]);

        $overview = app(DashboardOverview::class)->forUser($admin);
        $this->assertSame(0, $overview['cards']['opportunities']['value']);
        $this->assertNull($overview['cards']['opportunities']['url']);
        $this->assertSame(0, $overview['cards']['contracts']['value']);
        $this->assertSame([], $overview['attention']);
        $this->assertNull($overview['primary_url']);

        $this->actingAs($admin)->get('/crm/admin')->assertOk()
            ->assertDontSee('Hidden Contract')
            ->assertDontSee('รายการที่เกี่ยวข้องกับสัญญา')
            ->assertDontSee('>โครงการวิศวกรรม</p>', false)
            ->assertDontSee('ดูขั้นตอนการขาย');
    }

    public function test_progress_charts_use_current_stage_and_role_scope(): void
    {
        $first = $this->account('staff', 'First');
        $second = $this->account('staff', 'Second');
        $manager = $this->account('manager', 'Manager Charts');
        $admin = $this->account('admin', 'Admin Charts');
        $this->leadBatch([
            'signed-one' => ['site_name' => 'First signed', 'stage' => 'SIGNED'],
            'survey-two' => ['site_name' => 'Second survey', 'stage' => 'NEGOTIATING'],
            'signed-admin' => ['site_name' => 'Admin signed', 'stage' => 'SIGNED'],
        ]);
        Site::create(['name' => 'First signed', 'legacy_lead_id' => 'signed-one', 'owner_user_id' => $first->id]);
        Site::create(['name' => 'Second survey', 'legacy_lead_id' => 'survey-two', 'owner_user_id' => $second->id]);
        Site::create(['name' => 'Admin signed', 'legacy_lead_id' => 'signed-admin', 'owner_user_id' => $admin->id]);

        $projects = [];
        foreach (['signed-one' => 'LIVE', 'survey-two' => 'AWAITING_SURVEY', 'signed-admin' => 'LIVE'] as $lead => $stage) {
            $projects[$lead] = DB::table('crm_engineering_projects')->insertGetId([
                'lead_source_id' => $lead, 'project_code' => $lead, 'engineering_stage' => $stage,
                'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('crm_engineering_construction_records')->insert([
            'engineering_project_id' => $projects['signed-one'], 'lead_source_id' => 'signed-one',
            'owner_user_id' => $first->id, 'started_on' => '2026-10-20',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_survey_tasks')->insert([
            'engineering_project_id' => $projects['survey-two'], 'lead_source_id' => 'survey-two',
            'status' => 'scheduled', 'assignee_user_id' => $second->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ([[$first, 1, 1, 1, 1], [$manager, 2, 1, 2, 1], [$admin, 3, 2, 3, 2]] as [$user, $salesTotal, $signed, $projectTotal, $live]) {
            $charts = app(DashboardOverview::class)->forUser($user)['charts'];
            $this->assertSame($salesTotal, $charts['sales']['total']);
            $this->assertSame($signed, $charts['sales']['success']);
            $this->assertSame($projectTotal, $charts['engineering']['total']);
            $this->assertSame($live, $charts['engineering']['success']);
            $this->assertSame((int) round($signed * 100 / $salesTotal), $charts['sales']['percent']);
        }
        $this->assertSame(1, app(DashboardOverview::class)->forUser($first)['charts']['sales']['stages']['SIGNED']['count']);
        $this->assertSame(0, app(DashboardOverview::class)->forUser($first)['charts']['sales']['stages']['NEGOTIATING']['count']);

        DB::table('crm_pipeline_states')->insert([
            'lead_source_id' => 'signed-one', 'stage' => 'NEW', 'probability' => 10,
            'updated_by_user_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_projects')->where('id', $projects['signed-one'])
            ->update(['engineering_stage' => 'CONSTRUCTION']);
        $current = app(DashboardOverview::class)->forUser($first)['charts'];
        $this->assertSame(0, $current['sales']['success']);
        $this->assertSame(1, $current['sales']['stages']['NEW']['count']);
        $this->assertSame(0, $current['engineering']['success']);
    }

    public function test_progress_charts_handle_empty_data_and_removed_permissions(): void
    {
        $admin = $this->account('admin', 'Chart Empty');
        $charts = app(DashboardOverview::class)->forUser($admin)['charts'];
        $this->assertSame(0, $charts['sales']['total']);
        $this->assertSame(0, $charts['sales']['percent']);
        $this->assertSame(0, $charts['engineering']['percent']);

        DB::table('crm_role_permissions')->where('role_id', $admin->role_id)
            ->whereIn('permission', ['pipeline.view', 'engineering.view'])->delete();
        $admin->unsetRelation('role');
        $this->assertSame([], app(DashboardOverview::class)->forUser($admin)['charts']);
    }

    public function test_dashboard_renders_progress_charts_with_counts_and_drill_down(): void
    {
        $admin = $this->account('admin', 'Chart UI');
        $this->leadBatch([
            'signed' => ['site_name' => 'Signed Site', 'stage' => 'SIGNED'],
            'new' => ['site_name' => 'New Site', 'stage' => 'NEW'],
        ]);
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'signed', 'project_code' => 'CHART-1', 'engineering_stage' => 'LIVE',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get('/crm/admin')->assertOk()
            ->assertSee('ความสำเร็จการขาย')
            ->assertSee('ความคืบหน้างานวิศวกรรม')
            ->assertSee('ลงนามแล้ว 1 จาก 2')
            ->assertSee('เปิดบริการแล้ว 1 จาก 1')
            ->assertSee('href="'.route('crm.admin.pipeline.index', ['stage' => 'SIGNED']).'"', false)
            ->assertSee('aria-label="ความคืบหน้าขั้นตอนการขาย"', false);

        $this->actingAs($this->account('admin', 'Empty UI'))->get('/crm/admin')->assertOk()
            ->assertSee('ความสำเร็จการขาย');
    }

    private function account(string $role, string $name): User
    {
        return User::factory()->create(['name' => $name,
            'role_id' => DB::table('crm_roles')->where('slug', $role)->value('id')]);
    }

    private function leadBatch(array $leads): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'dashboard-test', 'exported_at' => '2026-10-26',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($leads as $sourceId => $payload) {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => $json, 'payload_sha256' => hash('sha256', $json),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function test_it_shows_a_clear_empty_state_before_crm_records_are_imported(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('crm.admin.dashboard'))
            ->assertOk()
            ->assertSee('ภาพรวมงาน')
            ->assertSee('ยังไม่มีข้อมูล CRM')
            ->assertSee('เริ่มจากการเพิ่มสถานที่และลูกค้า');
    }

    public function test_dashboard_core_content_follows_the_users_locale(): void
    {
        $english = $this->account('admin', 'English Dashboard');
        $english->update(['locale' => 'en']);
        $this->leadBatch([
            'negotiating' => ['site_name' => 'English Site', 'stage' => 'NEGOTIATING'],
        ]);
        $englishResponse = $this->actingAs($english)->get('/crm/admin');
        $englishResponse->assertOk()
            ->assertSee('Work overview')
            ->assertSee('Organization work status')
            ->assertSee('Sites in CRM')
            ->assertSee('Sales opportunities')
            ->assertSee('Sales success')
            ->assertSee('Contacted / negotiating')
            ->assertSee('View sales pipeline')
            ->assertDontSee('โอกาสขาย');
        $englishSystemContent = preg_replace('/<option value="th"[^>]*>ไทย<\/option>/', '', $englishResponse->getContent());
        $this->assertDoesNotMatchRegularExpression('/[ก-๙]/u', $englishSystemContent);

        $chinese = $this->account('admin', 'Chinese Dashboard');
        $chinese->update(['locale' => 'zh']);
        $this->actingAs($chinese)->get('/crm/admin')->assertOk()
            ->assertSee('工作概览')
            ->assertSee('全组织工作状态')
            ->assertSee('CRM 站点')
            ->assertSee('销售机会')
            ->assertSee('销售成果')
            ->assertSee('已联系 / 洽谈中');
    }
}
