<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class MyWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/crm/admin/my-work')->assertRedirect('/crm/login');
    }

    public function test_authenticated_user_sees_read_only_my_work_page(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-22 12:00:00'));
        $user = User::factory()->create(['name' => 'Kira']);
        $other = User::factory()->create();
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'my-work-test', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([
            'follow-up' => ['site_name' => 'Follow-up site', 'stage' => 'NEGOTIATING', 'next_action' => 'Call customer', 'next_action_at' => '2026-10-22 09:00:00'],
            'survey' => ['site_name' => 'Survey site', 'stage' => 'SIGNED'],
            'construction' => ['site_name' => 'Construction site', 'stage' => 'SIGNED'],
            'other' => ['site_name' => 'Other site', 'stage' => 'SIGNED', 'next_action' => 'Other action'],
            'legacy-name' => ['site_name' => 'Legacy label only', 'stage' => 'SIGNED', 'assigned_to' => 'Kira', 'next_action' => 'Do not assign by name'],
        ] as $sourceId => $payload) {
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => json_encode($payload), 'payload_sha256' => hash('sha256', $sourceId),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Site::create(['name' => 'Follow-up site', 'legacy_lead_id' => 'follow-up', 'owner_user_id' => $user->id]);
        Site::create(['name' => 'Other site', 'legacy_lead_id' => 'other', 'owner_user_id' => $other->id]);
        $surveyProject = DB::table('crm_engineering_projects')->insertGetId([
            'lead_source_id' => 'survey', 'project_code' => 'GSB-SURVEY', 'engineering_stage' => 'AWAITING_SURVEY',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_survey_tasks')->insert([
            'engineering_project_id' => $surveyProject, 'lead_source_id' => 'survey', 'status' => 'scheduled',
            'assignee_user_id' => $user->id, 'scheduled_at' => '2026-10-23 10:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $constructionProject = DB::table('crm_engineering_projects')->insertGetId([
            'lead_source_id' => 'construction', 'project_code' => 'GSB-CONSTRUCTION', 'engineering_stage' => 'CONSTRUCTION',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_construction_records')->insert([
            'engineering_project_id' => $constructionProject, 'lead_source_id' => 'construction',
            'owner_user_id' => $user->id, 'started_on' => '2026-10-21', 'target_finish_on' => '2026-10-22',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/crm/admin/my-work')->assertOk();
        $response->assertViewHas('items', function (array $items): bool {
            return array_column($items, 'source_id') === ['follow-up', 'construction', 'survey']
                && array_column($items, 'type') === ['follow_up', 'construction', 'survey']
                && array_column($items, 'overdue') === [true, false, false]
                && str_contains($items[2]['url'], '/crm/admin/engineering?open=survey');
        });
        $response->assertSee('Call customer')->assertSee('Survey site')->assertSee('Construction site')
            ->assertDontSee('Other action')->assertDontSee('Do not assign by name')
            ->assertDontSee('/crm/admin/pipeline?open=follow-up', false)
            ->assertSee('openFollowUp(item)', false);
        $this->get('/crm/admin/pipeline?open=follow-up&tab=activity')->assertOk()
            ->assertSee('x-text="displayValue(sections.profile?.next_action)"', false)
            ->assertSee('x-text="formatDate(sections.profile?.next_action_at)"', false);
        $this->get('/crm/admin/my-work')->assertOk();
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 1);
        $this->assertDatabaseCount('crm_engineering_construction_records', 1);
        $this->assertDatabaseCount('crm_legacy_records', 5);
    }

    public function test_empty_my_work_page_does_not_invent_unassigned_tasks(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/crm/admin/my-work')->assertOk()->assertSee('ยังไม่มีงานที่มอบหมาย');
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 0);
    }

    public function test_assigned_site_without_next_action_is_visible_as_site_not_as_task(): void
    {
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        Site::create(['name' => 'Assigned without action', 'owner_user_id' => $staff->id]);
        Site::create(['name' => 'Another owner site', 'owner_user_id' => User::factory()->create()->id]);

        $this->actingAs($staff)->get('/crm/admin/my-work')->assertOk()
            ->assertSee('Assigned without action')->assertDontSee('Another owner site')
            ->assertSee('สถานที่ที่รับผิดชอบ')->assertSee('ยังไม่มีงานที่มอบหมาย')
            ->assertDontSee('ตรวจและยืนยันผู้รับผิดชอบ');
    }

    public function test_staff_opens_assigned_opportunity_inside_my_work_instead_of_pipeline_directory(): void
    {
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        Site::create(['name' => 'Assigned lead', 'owner_user_id' => $staff->id, 'legacy_lead_id' => 'lead-1']);

        $this->actingAs($staff)->get('/crm/admin/my-work')->assertOk()
            ->assertSee('ดูรายละเอียดสถานที่')->assertSee('role="dialog"', false)
            ->assertSee('openSite(', false)
            ->assertDontSee('href="http://localhost/crm/admin/pipeline?open=lead-1"', false);
    }

    public function test_items_endpoint_returns_only_authenticated_users_work_and_summary(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-22 12:00:00'));
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $other = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('d', 64), 'source_name' => 'my-work-items', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([
            'mine' => ['site_name' => 'Mine', 'stage' => 'NEGOTIATING', 'next_action' => 'Call mine', 'next_action_at' => '2026-10-22 09:00:00'],
            'other' => ['site_name' => 'Other', 'stage' => 'NEGOTIATING', 'next_action' => 'Call other', 'next_action_at' => null],
        ] as $sourceId => $payload) {
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => json_encode($payload), 'payload_sha256' => hash('sha256', $sourceId),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Site::create(['name' => 'Mine', 'legacy_lead_id' => 'mine', 'owner_user_id' => $staff->id]);
        Site::create(['name' => 'Other', 'legacy_lead_id' => 'other', 'owner_user_id' => $other->id]);

        $this->actingAs($staff)->getJson('/crm/admin/my-work/items')->assertOk()
            ->assertJsonPath('items.0.source_id', 'mine')
            ->assertJsonPath('items.0.type', 'follow_up')
            ->assertJsonPath('sites.0.source_id', 'mine')
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('summary.overdue', 1)
            ->assertJsonPath('summary.undated', 0)
            ->assertJsonMissing(['source_id' => 'other']);
    }

    public function test_follow_up_task_uses_my_work_panel_instead_of_pipeline_link(): void
    {
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        Site::create(['name' => 'Assigned lead', 'owner_user_id' => $staff->id, 'legacy_lead_id' => 'lead-1']);

        $this->actingAs($staff)->get('/crm/admin/my-work')->assertOk()
            ->assertSee('ติดตามและเข้าพบ')
            ->assertSee('openFollowUp(item)', false)
            ->assertDontSee('/crm/admin/pipeline?open=lead-1', false);
    }

    public function test_staff_can_log_an_assigned_follow_up_but_cannot_write_another_staff_site(): void
    {
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $other = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('e', 64), 'source_name' => 'my-work-visit', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (['mine', 'other'] as $sourceId) {
            $payload = json_encode(['site_name' => $sourceId, 'stage' => 'NEGOTIATING']);
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Site::create(['name' => 'Mine', 'legacy_lead_id' => 'mine', 'owner_user_id' => $staff->id]);
        Site::create(['name' => 'Other', 'legacy_lead_id' => 'other', 'owner_user_id' => $other->id]);
        $visit = [
            'visit_type' => 'FOLLOW_UP', 'occurred_at' => '2026-10-22T10:00',
            'internal_participants' => 'GSB', 'summary' => 'Called customer',
            'next_steps' => 'Send proposal',
        ];

        $this->actingAs($staff)->postJson('/crm/admin/pipeline/mine/visits', $visit)->assertCreated();
        $this->postJson('/crm/admin/pipeline/other/visits', $visit)->assertForbidden();
        $this->assertDatabaseHas('crm_visit_logs', ['lead_source_id' => 'mine', 'created_by_user_id' => $staff->id]);
        $this->assertDatabaseMissing('crm_visit_logs', ['lead_source_id' => 'other']);
    }

    public function test_staff_can_manage_files_for_an_assigned_site_from_my_work_only(): void
    {
        Storage::fake('local');
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $other = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('f', 64), 'source_name' => 'my-work-files', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (['mine', 'other'] as $sourceId) {
            $payload = json_encode(['site_name' => $sourceId, 'stage' => 'NEGOTIATING']);
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Site::create(['name' => 'Mine', 'legacy_lead_id' => 'mine', 'owner_user_id' => $staff->id]);
        Site::create(['name' => 'Other', 'legacy_lead_id' => 'other', 'owner_user_id' => $other->id]);

        $page = $this->actingAs($staff)->get('/crm/admin/my-work')->assertOk();
        $page->assertSee('ไฟล์และภาพหน้างาน')->assertSee('uploadAttachment()', false);

        $fileId = $this->postJson('/crm/admin/pipeline/mine/attachments', [
            'category' => 'SITE_PHOTO', 'note' => 'Assigned site front view',
            'file' => UploadedFile::fake()->image('assigned-site.jpg'),
        ])->assertCreated()->json('file.id');
        $this->getJson('/crm/admin/pipeline/mine')->assertOk()
            ->assertJsonPath('sections.attachments.0.file_name', 'assigned-site.jpg')
            ->assertJsonPath('sections.attachments.0.note', 'Assigned site front view');
        $this->get('/crm/admin/pipeline/mine/attachments/'.$fileId)->assertOk();

        $this->postJson('/crm/admin/pipeline/other/attachments', [
            'category' => 'SITE_PHOTO', 'file' => UploadedFile::fake()->image('forbidden.jpg'),
        ])->assertForbidden();
        $this->get('/crm/admin/pipeline/other/attachments/'.$fileId)->assertForbidden();
        $this->assertDatabaseCount('crm_attachments', 1);
    }
}
