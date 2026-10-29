<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class PipelineVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_visit_is_stored_separately_from_legacy_import(): void
    {
        $batchId = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'fixture.json',
            'exported_at' => '2026-10-16T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $payload = json_encode(['id' => 'lead-1', 'site_name' => 'Test site', 'stage' => 'NEW']);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batchId, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson('/crm/admin/pipeline/lead-1/visits', [
            'visit_type' => 'FOLLOW_UP', 'occurred_at' => '2026-10-19T10:00',
            'internal_participants' => 'GSB', 'summary' => 'Called customer',
            'next_steps' => 'Send proposal',
        ])->assertCreated();

        $this->assertDatabaseHas('crm_visit_logs', ['lead_source_id' => 'lead-1', 'summary' => 'Called customer']);
        $this->assertSame($payload, DB::table('crm_legacy_records')->where('source_id', 'lead-1')->value('payload'));
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.visits.0.summary', 'Called customer')
            ->assertJsonPath('sections.visits.0.origin', 'current')
            ->assertJsonPath('sections.profile.next_action', 'Send proposal');

        $visitId = $response->json('visit.id');
        $this->patchJson('/crm/admin/pipeline/lead-1/visits/'.$visitId, [
            'visit_type' => 'MEETING', 'occurred_at' => '2026-10-19T11:00',
            'internal_participants' => 'GSB', 'summary' => 'Met customer',
            'next_steps' => 'Review terms',
        ])->assertOk();
        $this->getJson('/crm/admin/pipeline/lead-1')->assertJsonPath('sections.visits.0.summary', 'Met customer');
        $this->patchJson('/crm/admin/pipeline/lead-1/visits/999999', [
            'visit_type' => 'MEETING', 'occurred_at' => '2026-10-19T11:00',
            'internal_participants' => 'GSB', 'summary' => 'Wrong',
            'next_steps' => 'Wrong',
        ])->assertNotFound();

        $newBatch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('c', 64), 'source_name' => 'new-export.json',
            'exported_at' => '2026-10-19T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $newBatch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->getJson('/crm/admin/pipeline/lead-1')->assertJsonPath('sections.visits.0.summary', 'Met customer');
        $this->assertDatabaseHas('crm_legacy_records', ['batch_id' => $batchId, 'source_id' => 'lead-1', 'payload' => $payload]);
    }

    public function test_guest_and_invalid_visit_are_rejected(): void
    {
        $this->postJson('/crm/admin/pipeline/missing/visits', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson('/crm/admin/pipeline/missing/visits', [
            'visit_type' => 'FOLLOW_UP', 'occurred_at' => '2026-10-19T10:00',
            'internal_participants' => 'GSB', 'summary' => 'Called', 'next_steps' => 'Call again',
        ])->assertNotFound();
    }
}
