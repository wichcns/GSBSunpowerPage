<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use App\Crm\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class PipelineStageTest extends TestCase
{
    use RefreshDatabase;

    private function lead(string $stage = 'MOU'): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'test',
            'exported_at' => '2026-10-20', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Test Site', 'stage' => $stage, 'probability' => 65]),
            'payload_sha256' => str_repeat('b', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_contract_transition_creates_one_engineering_project_and_pending_event(): void
    {
        $this->lead();

        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');

        $this->patchJson('/crm/admin/pipeline/lead-1/stage', [
            'stage' => 'CONTRACT', 'expected_version' => $version,
        ])->assertOk()->assertJsonPath('opportunity.stage_code', 'CONTRACT');

        $this->assertDatabaseCount('crm_engineering_projects', 1);
        $this->assertDatabaseHas('crm_integration_outbox', [
            'event_type' => 'crm.project.created', 'status' => 'pending_connection',
        ]);
        $this->assertDatabaseHas('crm_pipeline_states', ['lead_source_id' => 'lead-1', 'stage' => 'CONTRACT', 'probability' => 82]);
        $site = Site::create(['site_code' => 'TEST-SITE-1', 'name' => 'Test Site', 'legacy_lead_id' => 'lead-1']);
        $this->getJson('/crm/admin/sites/'.$site->id)->assertOk()
            ->assertJsonPath('site.stage_code', 'CONTRACT')
            ->assertJsonPath('site.stage_label', 'ขั้นตอนสัญญา');
        $this->getJson('/crm/admin/pipeline/lead-1')
            ->assertJsonPath('sections.technical.project.origin', 'current')
            ->assertJsonPath('sections.technical.project.sync_status', 'pending_connection');
    }

    public function test_signed_after_contract_reuses_the_project_and_rejects_a_stale_edit(): void
    {
        $this->lead();
        $this->actingAs(User::factory()->create());
        $legacyVersion = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CONTRACT', 'expected_version' => $legacyVersion])->assertOk();
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'SIGNED', 'expected_version' => $legacyVersion])->assertStatus(409);
        $currentVersion = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'SIGNED', 'expected_version' => $currentVersion])->assertOk();

        $this->assertDatabaseCount('crm_engineering_projects', 1);
        $this->assertDatabaseCount('crm_integration_outbox', 1);
        $this->assertDatabaseCount('crm_stage_histories', 2);
        $this->get('/crm/admin/contracts')->assertOk()->assertSee('ลงนามแล้ว');
        $signedVersion = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'SIGNED', 'expected_version' => $signedVersion])->assertOk();
        $this->assertDatabaseCount('crm_stage_histories', 2);
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'MOU', 'expected_version' => $signedVersion])->assertOk();
        $this->assertDatabaseCount('crm_engineering_projects', 1);
        $this->assertDatabaseCount('crm_integration_outbox', 1);
        $mouVersion = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CONTRACT', 'expected_version' => $mouVersion])->assertOk();
        $this->assertDatabaseCount('crm_engineering_projects', 1);
        $this->assertDatabaseCount('crm_integration_outbox', 1);
    }

    public function test_legacy_project_is_linked_without_new_event(): void
    {
        $this->lead();
        $batch = DB::table('crm_legacy_import_batches')->value('id');
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'projects', 'source_id' => 'project-1',
            'payload' => json_encode(['id' => 'project-1', 'lead_id' => 'lead-1',
                'project_code' => 'GSB-OLD-1', 'engineering_stage' => 'SURVEYED', 'sync_status' => 'queued']),
            'payload_sha256' => str_repeat('c', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CONTRACT', 'expected_version' => $version])
            ->assertOk()->assertJsonPath('project.project_code', 'GSB-OLD-1');
        $this->assertDatabaseHas('crm_engineering_projects', ['legacy_project_id' => 'project-1', 'origin' => 'legacy']);
        $this->assertDatabaseCount('crm_integration_outbox', 0);
    }

    public function test_unknown_stage_can_be_read_but_cannot_be_written(): void
    {
        $this->lead('CUSTOM');
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CUSTOM', 'expected_version' => $version])->assertStatus(422);
        $this->assertDatabaseCount('crm_pipeline_states', 0);
    }

    public function test_a_new_import_does_not_replace_a_stage_edited_in_crm(): void
    {
        $this->lead();
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'REVIEW', 'expected_version' => $version])->assertOk();
        $newBatch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('d', 64), 'source_name' => 'new-test', 'exported_at' => '2026-10-21',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $newBatch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Updated source', 'stage' => 'SIGNED', 'probability' => 100]),
            'payload_sha256' => str_repeat('e', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('opportunity.stage_code', 'REVIEW')
            ->assertJsonPath('opportunity.probability_raw', 45);
        $this->assertDatabaseCount('crm_engineering_projects', 0);
    }

    public function test_conflicting_legacy_projects_block_the_stage_change_atomically(): void
    {
        $this->lead();
        $batch = DB::table('crm_legacy_import_batches')->value('id');
        foreach (['project-1', 'project-2'] as $id) {
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'projects', 'source_id' => $id,
                'payload' => json_encode(['id' => $id, 'lead_id' => 'lead-1', 'project_code' => 'GSB-'.$id]),
                'payload_sha256' => hash('sha256', $id), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CONTRACT', 'expected_version' => $version])->assertStatus(409);
        $this->assertDatabaseCount('crm_pipeline_states', 0);
        $this->assertDatabaseCount('crm_stage_histories', 0);
        $this->assertDatabaseCount('crm_engineering_projects', 0);
        $this->assertDatabaseCount('crm_integration_outbox', 0);
    }

    public function test_every_legacy_sales_stage_has_the_expected_probability(): void
    {
        $this->lead();
        $this->actingAs(User::factory()->create());
        foreach ([
            'NEW' => 10, 'NEGOTIATING' => 30, 'REVIEW' => 45, 'MOU' => 65,
            'CONTRACT' => 82, 'SIGNED' => 100, 'ON_HOLD' => 15, 'CANCELLED' => 0,
        ] as $stage => $probability) {
            $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
            $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => $stage, 'expected_version' => $version])
                ->assertOk()->assertJsonPath('opportunity.stage_code', $stage)
                ->assertJsonPath('opportunity.probability_raw', $probability);
        }
        $this->assertDatabaseCount('crm_engineering_projects', 1);
        $this->assertDatabaseCount('crm_integration_outbox', 1);
    }
}
