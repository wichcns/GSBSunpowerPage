<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class EngineeringTest extends TestCase
{
    use RefreshDatabase;

    private function seedLegacyProject(int $count = 1): array
    {
        $batchHash = DB::table('crm_legacy_import_batches')->count() === 0 ? '1' : '8';
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat($batchHash, 64), 'source_name' => 'test', 'exported_at' => '2026-10-20',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Station A', 'stage' => 'CONTRACT', 'probability' => 82]),
            'payload_sha256' => str_repeat('2', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        for ($i = 0; $i < $count; $i++) {
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'projects', 'source_id' => 'project-'.$i,
                'payload' => json_encode(['id' => 'project-'.$i, 'lead_id' => 'lead-1', 'project_code' => 'OLD-'.$i, 'engineering_stage' => 'SURVEYED']),
                'payload_sha256' => str_repeat((string) ($i + 3), 64), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return [$batch, "legacy:{$batch}:".str_repeat('3', 64)];
    }

    public function test_engineering_page_renders_chinese_without_thai_system_copy(): void
    {
        $response = $this->actingAs(User::factory()->create(['locale' => 'zh']))
            ->get('/crm/admin/engineering')->assertOk()
            ->assertSee('工程工作')->assertSee('项目列表')->assertSee('投入运营');

        $systemContent = preg_replace('/<option value="th"[^>]*>ไทย<\/option>/', '', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/[ก-๙]/u', $systemContent);
    }

    public function test_legacy_project_creates_overlay_without_changing_import(): void
    {
        [, $version] = $this->seedLegacyProject();
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'AWAITING_SURVEY', 'expected_version' => $version,
        ])->assertOk()->assertJsonPath('project.project_code', 'OLD-0');
        $this->assertDatabaseHas('crm_engineering_projects', ['lead_source_id' => 'lead-1', 'legacy_project_id' => 'project-0', 'engineering_stage' => 'AWAITING_SURVEY']);
        $this->assertDatabaseCount('crm_legacy_records', 2);
        $this->assertDatabaseCount('crm_engineering_stage_histories', 1);
        $this->assertDatabaseCount('crm_integration_outbox', 1);
    }

    public function test_duplicate_legacy_projects_and_stale_version_do_not_write(): void
    {
        [, $version] = $this->seedLegacyProject(2);
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'LIVE', 'expected_version' => $version,
        ])->assertStatus(409);
        $this->assertDatabaseCount('crm_engineering_projects', 0);
        $this->assertDatabaseCount('crm_pipeline_states', 0);
    }

    public function test_noop_and_stale_current_version_do_not_create_events(): void
    {
        [$batch] = $this->seedLegacyProject();
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'lead-1', 'project_code' => 'OLD-0', 'legacy_project_id' => 'project-0',
            'engineering_stage' => 'SURVEYED', 'sync_status' => 'pending_connection', 'origin' => 'legacy',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'SURVEYED', 'expected_version' => "current:{$batch}:1",
        ])->assertOk();
        $this->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'LIVE', 'expected_version' => 'current:0',
        ])->assertStatus(409);
        $this->assertDatabaseCount('crm_engineering_stage_histories', 0);
        $this->assertDatabaseCount('crm_integration_outbox', 0);
    }

    public function test_invalid_stage_and_guest_cannot_change_project(): void
    {
        [, $version] = $this->seedLegacyProject();
        $payload = ['engineering_stage' => 'LIVE', 'expected_version' => $version];
        $this->patchJson('/crm/admin/engineering/lead-1/stage', $payload)->assertUnauthorized();
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'INVALID', 'expected_version' => $version,
        ])->assertUnprocessable();
        $this->assertDatabaseCount('crm_engineering_projects', 0);
    }

    public function test_legacy_version_is_rejected_after_new_import_batch(): void
    {
        [, $version] = $this->seedLegacyProject();
        $nextBatch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('8', 64), 'source_name' => 'new-test', 'exported_at' => '2026-10-21',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (DB::table('crm_legacy_records')->where('batch_id', $nextBatch - 1)->get() as $record) {
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $nextBatch, 'source_table' => $record->source_table,
                'source_id' => $record->source_id, 'payload' => $record->payload,
                'payload_sha256' => $record->payload_sha256,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'LIVE', 'expected_version' => $version,
        ])->assertStatus(409);
        $this->assertDatabaseCount('crm_engineering_projects', 0);
    }

    public function test_current_project_version_becomes_stale_after_new_import_batch(): void
    {
        $this->seedLegacyProject();
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'lead-1', 'project_code' => 'OLD-0', 'legacy_project_id' => 'project-0',
            'engineering_stage' => 'SURVEYED', 'sync_status' => 'pending_connection', 'origin' => 'legacy',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/engineering/lead-1')->json('project.version');
        $this->seedLegacyProject();

        $this->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'CONSTRUCTION', 'expected_version' => $version,
        ])->assertStatus(409);
        $this->assertDatabaseHas('crm_engineering_projects', ['lead_source_id' => 'lead-1', 'engineering_stage' => 'SURVEYED']);
    }

    public function test_outbox_failure_rolls_back_project_sales_and_histories(): void
    {
        [$batch] = $this->seedLegacyProject();
        $projectId = DB::table('crm_engineering_projects')->insertGetId([
            'lead_source_id' => 'lead-1', 'project_code' => 'OLD-0', 'legacy_project_id' => 'project-0',
            'engineering_stage' => 'SURVEYED', 'sync_status' => 'pending_connection', 'origin' => 'legacy',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_integration_outbox')->insert([
            'engineering_project_id' => $projectId,
            'event_type' => 'crm.project.engineering_stage_changed',
            'dedupe_key' => 'crm.project.engineering_stage_changed:'.$projectId.':1:CONSTRUCTION',
            'status' => 'pending_connection', 'payload' => '{}',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        try {
            $this->actingAs(User::factory()->create())->patchJson('/crm/admin/engineering/lead-1/stage', [
                'engineering_stage' => 'CONSTRUCTION', 'expected_version' => "current:{$batch}:1",
            ]);
        } catch (\Throwable) {
            // The unique outbox key deliberately forces the transaction to fail.
        }
        $this->assertDatabaseHas('crm_engineering_projects', ['id' => $projectId, 'engineering_stage' => 'SURVEYED', 'version' => 1]);
        $this->assertDatabaseCount('crm_pipeline_states', 0);
        $this->assertDatabaseCount('crm_stage_histories', 0);
        $this->assertDatabaseCount('crm_engineering_stage_histories', 0);
    }

    public function test_construction_updates_engineering_and_sales_states_atomically(): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('e', 64), 'source_name' => 'test', 'exported_at' => '2026-10-20',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Station A', 'stage' => 'CONTRACT', 'probability' => 82]),
            'payload_sha256' => str_repeat('f', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'lead-1', 'project_code' => 'GSB-CRM-TEST',
            'engineering_stage' => 'SURVEYED', 'sync_status' => 'pending_connection',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->patchJson('/crm/admin/engineering/lead-1/stage', [
                'engineering_stage' => 'CONSTRUCTION', 'expected_version' => "current:{$batch}:1",
            ])->assertOk()->assertJsonPath('project.engineering_stage', 'CONSTRUCTION');

        $this->assertDatabaseHas('crm_pipeline_states', ['lead_source_id' => 'lead-1', 'stage' => 'SIGNED', 'probability' => 100]);
        $this->assertDatabaseHas('crm_engineering_projects', ['lead_source_id' => 'lead-1', 'engineering_stage' => 'CONSTRUCTION']);
        $this->assertDatabaseCount('crm_stage_histories', 1);
    }

    public function test_project_detail_stays_in_engineering_and_does_not_write_data(): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('c', 64), 'source_name' => 'test', 'exported_at' => '2026-10-20',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Station A', 'stage' => 'CONTRACT']),
            'payload_sha256' => str_repeat('d', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'lead-1', 'project_code' => 'GSB-CRM-TEST',
            'engineering_stage' => 'AWAITING_SURVEY', 'sync_status' => 'pending_connection',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get('/crm/admin/engineering/lead-1')->assertRedirect('/crm/login');
        $this->actingAs(User::factory()->create())->get('/crm/admin/engineering')
            ->assertOk()->assertSee('crm-engineering')->assertSee('ดูโครงการ')->assertSee('รอสำรวจ');
        $this->getJson('/crm/admin/engineering/lead-1')->assertOk()
            ->assertJsonPath('project.project_code', 'GSB-CRM-TEST')
            ->assertJsonPath('project.engineering_stage', 'AWAITING_SURVEY');
        $this->getJson('/crm/admin/engineering/missing')->assertNotFound();
        $this->assertDatabaseCount('crm_engineering_projects', 1);
        $this->assertDatabaseCount('crm_integration_outbox', 0);
    }

    public function test_engineering_page_shows_one_project_and_truthful_pending_status(): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'test', 'exported_at' => '2026-10-20',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Station A', 'stage' => 'MOU']),
            'payload_sha256' => str_repeat('b', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'lead-1', 'project_code' => 'GSB-CRM-TEST',
            'engineering_stage' => 'NOT_STARTED', 'sync_status' => 'pending_connection',
            'origin' => 'current', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get('/crm/admin/engineering')->assertRedirect('/crm/login');
        $this->actingAs(User::factory()->create())->get('/crm/admin/engineering')->assertOk()
            ->assertSee('Station A')->assertSee('GSB-CRM-TEST')->assertSee('ยังไม่เริ่ม')->assertSee('รอเชื่อมต่อ')
            ->assertDontSee('ส่งแล้ว');
    }
}
