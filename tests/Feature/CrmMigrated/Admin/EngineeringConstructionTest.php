<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class EngineeringConstructionTest extends TestCase
{
    use RefreshDatabase;

    private function seedProject(string $stage = 'SURVEYED', bool $current = true): string
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'construction-test', 'exported_at' => '2026-10-21',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            ['batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
                'payload' => json_encode(['site_name' => 'Construction Site', 'stage' => 'CONTRACT', 'probability' => 82, 'installation_status' => 'scheduled']),
                'payload_sha256' => str_repeat('b', 64), 'created_at' => now(), 'updated_at' => now()],
            ['batch_id' => $batch, 'source_table' => 'projects', 'source_id' => 'project-1',
                'payload' => json_encode(['id' => 'project-1', 'lead_id' => 'lead-1', 'project_code' => 'GSB-CON-1', 'engineering_stage' => $stage]),
                'payload_sha256' => str_repeat('c', 64), 'created_at' => now(), 'updated_at' => now()],
        ]);
        if ($current) {
            DB::table('crm_engineering_projects')->insert([
                'lead_source_id' => 'lead-1', 'project_code' => 'GSB-CON-1', 'legacy_project_id' => 'project-1',
                'engineering_stage' => $stage, 'sync_status' => 'pending_connection', 'origin' => 'legacy',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $current ? "current:{$batch}:1" : "legacy:{$batch}:".str_repeat('c', 64);
    }

    public function test_starting_construction_saves_owner_and_dates_and_preserves_manual_installation_status(): void
    {
        $version = $this->seedProject();
        $actor = User::factory()->create();
        $this->actingAs($actor);
        $owner = User::factory()->create();

        $this->postJson('/crm/admin/engineering/lead-1/construction/start', [
            'owner_user_id' => $owner->id, 'started_on' => '2026-10-21', 'target_finish_on' => '2026-11-30',
            'note' => 'เริ่มเตรียมพื้นที่', 'expected_project_version' => $version,
        ])->assertOk()->assertJsonPath('project.engineering_stage', 'CONSTRUCTION')
            ->assertJsonPath('construction.owner_user_id', $owner->id);

        $this->assertDatabaseHas('crm_pipeline_states', ['lead_source_id' => 'lead-1', 'stage' => 'CONTRACT_SIGNED']);
        $this->assertDatabaseHas('crm_engineering_construction_records', [
            'lead_source_id' => 'lead-1', 'started_on' => '2026-10-21', 'updated_by_user_id' => $actor->id,
        ]);
        $this->assertDatabaseCount('crm_engineering_construction_updates', 1);
        $lead = DB::table('crm_legacy_records')->where('source_table', 'leads')->first();
        $this->assertSame('scheduled', json_decode($lead->payload, true)['installation_status']);
    }

    public function test_legacy_construction_is_readable_without_writing_on_get(): void
    {
        $this->seedProject('CONSTRUCTION', false);
        $this->actingAs(User::factory()->create());
        $this->getJson('/crm/admin/engineering/lead-1')->assertOk()
            ->assertJsonPath('construction.status', 'not_recorded')
            ->assertJsonPath('construction.installation_status', 'scheduled');
        $this->assertDatabaseCount('crm_engineering_construction_records', 0);
        $this->assertDatabaseCount('crm_engineering_projects', 0);
    }

    public function test_invalid_dates_and_stale_version_do_not_start_construction(): void
    {
        $version = $this->seedProject();
        $this->actingAs(User::factory()->create());
        $owner = User::factory()->create();
        $payload = ['owner_user_id' => $owner->id, 'started_on' => '2026-10-21',
            'target_finish_on' => '2026-10-20', 'expected_project_version' => $version];
        $this->postJson('/crm/admin/engineering/lead-1/construction/start', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('crm_engineering_construction_records', 0);
        $this->postJson('/crm/admin/engineering/lead-1/construction/start', [...$payload,
            'target_finish_on' => '2026-11-20', 'expected_project_version' => 'stale'])->assertConflict();
        $this->assertDatabaseHas('crm_engineering_projects', ['lead_source_id' => 'lead-1', 'engineering_stage' => 'SURVEYED']);
    }

    public function test_construction_update_appends_timeline_and_rejects_stale_replay(): void
    {
        $version = $this->seedProject();
        $this->actingAs(User::factory()->create());
        $owner = User::factory()->create();
        $this->postJson('/crm/admin/engineering/lead-1/construction/start', [
            'owner_user_id' => $owner->id, 'started_on' => '2026-10-21', 'expected_project_version' => $version,
        ])->assertOk();
        $current = $this->getJson('/crm/admin/engineering/lead-1')->json('construction');
        $payload = ['note' => 'ติดตั้งระบบไฟฟ้ารอบแรก', 'expected_version' => $current['version']];
        $this->postJson('/crm/admin/engineering/lead-1/construction/updates', $payload)->assertOk()
            ->assertJsonCount(1, 'construction.updates');
        $this->postJson('/crm/admin/engineering/lead-1/construction/updates', $payload)->assertConflict();
        $this->assertDatabaseCount('crm_engineering_construction_updates', 1);
    }

    public function test_existing_legacy_construction_can_add_tracking_without_changing_its_stage(): void
    {
        $version = $this->seedProject('CONSTRUCTION', false);
        $this->actingAs(User::factory()->create());
        $owner = User::factory()->create();
        $this->patchJson('/crm/admin/engineering/lead-1/construction', [
            'owner_user_id' => $owner->id, 'started_on' => '2026-10-21',
            'target_finish_on' => null, 'expected_version' => null, 'expected_project_version' => $version,
        ])->assertOk()->assertJsonPath('construction.status', 'recorded')
            ->assertJsonPath('project.engineering_stage', 'CONSTRUCTION');
        $this->assertDatabaseCount('crm_engineering_construction_records', 1);
        $this->assertDatabaseCount('crm_engineering_construction_updates', 0);
        $this->assertDatabaseCount('crm_pipeline_states', 0);
        $this->assertDatabaseCount('crm_integration_outbox', 0);
    }

    public function test_go_live_keeps_construction_history_and_rejects_stale_repeat(): void
    {
        $version = $this->seedProject('CONSTRUCTION');
        $actor = User::factory()->create();
        $owner = User::factory()->create();
        $this->actingAs($actor);
        $this->patchJson('/crm/admin/engineering/lead-1/construction', [
            'owner_user_id' => $owner->id, 'started_on' => '2026-10-21',
            'target_finish_on' => '2026-11-30', 'expected_version' => null,
            'expected_project_version' => $version,
        ])->assertOk();
        $constructionVersion = $this->getJson('/crm/admin/engineering/lead-1')->json('construction.version');
        $this->postJson('/crm/admin/engineering/lead-1/construction/updates', [
            'note' => 'ติดตั้งและทดสอบแล้ว', 'expected_version' => $constructionVersion,
        ])->assertOk();

        $payload = ['engineering_stage' => 'LIVE', 'expected_version' => $version];
        $this->patchJson('/crm/admin/engineering/lead-1/stage', $payload)
            ->assertOk()->assertJsonPath('project.engineering_stage', 'LIVE')
            ->assertJsonPath('construction.owner_user_id', $owner->id)
            ->assertJsonCount(1, 'construction.updates')
            ->assertJsonPath('stage_history.0.from_stage', 'CONSTRUCTION')
            ->assertJsonPath('stage_history.0.to_stage', 'LIVE')
            ->assertJsonPath('stage_history.0.actor_name', $actor->name);
        $this->patchJson('/crm/admin/engineering/lead-1/stage', $payload)->assertConflict();
        $this->getJson('/crm/admin/engineering/lead-1')->assertOk()
            ->assertJsonCount(1, 'construction.updates')
            ->assertJsonPath('stage_history.0.to_stage', 'LIVE');

        $this->assertDatabaseHas('crm_engineering_stage_histories', [
            'lead_source_id' => 'lead-1', 'from_stage' => 'CONSTRUCTION',
            'to_stage' => 'LIVE', 'actor_user_id' => $actor->id,
        ]);
        $this->assertDatabaseCount('crm_engineering_stage_histories', 1);
        $this->assertDatabaseCount('crm_engineering_construction_records', 1);
        $this->assertDatabaseCount('crm_engineering_construction_updates', 1);
        $this->assertDatabaseHas('crm_pipeline_states', ['lead_source_id' => 'lead-1', 'stage' => 'CONTRACT_SIGNED']);
        $this->assertDatabaseCount('crm_integration_outbox', 1);
        $lead = DB::table('crm_legacy_records')->where('source_table', 'leads')->first();
        $this->assertSame('scheduled', json_decode($lead->payload, true)['installation_status']);
    }
}
