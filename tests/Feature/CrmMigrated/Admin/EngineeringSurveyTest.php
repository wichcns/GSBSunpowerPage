<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class EngineeringSurveyTest extends TestCase
{
    use RefreshDatabase;

    private function seedProject(string $stage = 'AWAITING_SURVEY', bool $current = true): array
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'survey-test', 'exported_at' => '2026-10-21',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Survey Site', 'stage' => 'CONTRACT', 'probability' => 82]),
            'payload_sha256' => str_repeat('b', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'projects', 'source_id' => 'project-1',
            'payload' => json_encode(['id' => 'project-1', 'lead_id' => 'lead-1', 'project_code' => 'GSB-SURVEY-1', 'engineering_stage' => $stage]),
            'payload_sha256' => str_repeat('c', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        if ($current) {
            DB::table('crm_engineering_projects')->insert([
                'lead_source_id' => 'lead-1', 'project_code' => 'GSB-SURVEY-1', 'legacy_project_id' => 'project-1',
                'engineering_stage' => $stage, 'sync_status' => 'pending_connection',
                'origin' => 'legacy', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return [$batch, $current ? "current:{$batch}:1" : "legacy:{$batch}:".str_repeat('c', 64)];
    }

    public function test_existing_waiting_project_is_visible_as_unassigned_without_writing_on_read(): void
    {
        $this->seedProject();
        $this->actingAs(User::factory()->create());
        $this->get('/crm/admin/engineering')->assertOk()->assertSee('งานรอสำรวจ');
        $this->getJson('/crm/admin/engineering/lead-1')->assertOk()
            ->assertJsonPath('survey_task.status', 'unassigned');
        $this->getJson('/crm/admin/engineering/lead-1')->assertOk();
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 0);
    }

    public function test_entering_waiting_survey_creates_one_task_and_leaving_cancels_it(): void
    {
        [, $version] = $this->seedProject('SURVEYED');
        $this->actingAs(User::factory()->create());
        $this->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'AWAITING_SURVEY', 'expected_version' => $version,
        ])->assertOk();
        $this->assertDatabaseHas('crm_engineering_survey_tasks', ['lead_source_id' => 'lead-1', 'status' => 'unassigned']);
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 1);

        $nextVersion = $this->getJson('/crm/admin/engineering/lead-1')->json('project.version');
        $this->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'NOT_STARTED', 'expected_version' => $nextVersion,
        ])->assertOk();
        $this->assertDatabaseHas('crm_engineering_survey_tasks', ['lead_source_id' => 'lead-1', 'status' => 'cancelled']);
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 1);
    }

    public function test_assigning_and_completing_survey_advances_only_engineering_stage(): void
    {
        $this->seedProject();
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $this->actingAs($actor);
        $taskVersion = $this->getJson('/crm/admin/engineering/lead-1')->json('survey_task.version');
        $this->patchJson('/crm/admin/engineering/lead-1/survey', [
            'assignee_user_id' => $assignee->id,
            'scheduled_at' => '2026-10-25 10:00:00',
            'expected_version' => $taskVersion,
        ])->assertOk()->assertJsonPath('survey_task.status', 'scheduled');
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 1);

        $latest = $this->getJson('/crm/admin/engineering/lead-1')->json();
        $this->postJson('/crm/admin/engineering/lead-1/survey/complete', [
            'result_summary' => 'ตรวจพื้นที่และไฟฟ้าแล้ว',
            'expected_version' => $latest['survey_task']['version'],
            'expected_project_version' => $latest['project']['version'],
        ])->assertOk()->assertJsonPath('project.engineering_stage', 'SURVEYED')
            ->assertJsonPath('survey_task.status', 'done');
        $this->assertDatabaseHas('crm_engineering_survey_tasks', ['lead_source_id' => 'lead-1', 'status' => 'done']);
        $this->assertDatabaseCount('crm_pipeline_states', 0);
    }

    public function test_invalid_assignment_and_stale_survey_version_do_not_change_saved_work(): void
    {
        $this->seedProject();
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/engineering/lead-1')->json('survey_task.version');
        $this->patchJson('/crm/admin/engineering/lead-1/survey', [
            'assignee_user_id' => 999999, 'scheduled_at' => '2026-10-25 10:00:00', 'expected_version' => $version,
        ])->assertUnprocessable();
        $this->assertDatabaseCount('crm_engineering_survey_tasks', 0);

        $assignee = User::factory()->create();
        $payload = ['assignee_user_id' => $assignee->id, 'scheduled_at' => '2026-10-25 10:00:00', 'expected_version' => $version];
        $first = $this->patchJson('/crm/admin/engineering/lead-1/survey', $payload)->assertOk();
        $this->patchJson('/crm/admin/engineering/lead-1/survey', $payload)->assertConflict();
        $this->assertSame(1, DB::table('crm_engineering_survey_histories')->where('event_code', 'scheduled')->count());
        $this->assertSame($first->json('survey_task.version'), $this->getJson('/crm/admin/engineering/lead-1')->json('survey_task.version'));
    }
}
