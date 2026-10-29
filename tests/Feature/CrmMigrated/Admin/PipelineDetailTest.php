<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class PipelineDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_returns_five_read_only_sections_without_private_metadata_or_writes(): void
    {
        $batch = $this->batch();
        $this->record($batch, 'leads', 'lead-1', ['site_name' => 'Site One', 'stage' => 'SIGNED']);
        $this->record($batch, 'activities', 'activity-1', [
            'lead_id' => 'lead-1', 'summary' => 'Call', 'actor_email' => 'private@example.com',
        ]);
        $this->record($batch, 'attachments', 'file-1', [
            'lead_id' => 'lead-1', 'file_name' => 'photo.jpg', 'object_key' => 'private/object',
        ]);
        $count = DB::table('crm_legacy_records')->count();

        $response = $this->actingAs(User::factory()->create())->getJson('/crm/admin/pipeline/lead-1');

        $response->assertOk()
            ->assertJsonPath('opportunity.stage_code', 'SIGNED')
            ->assertJsonPath('sections.profile.site_name', 'Site One')
            ->assertJsonPath('sections.activities.0.summary', 'Call')
            ->assertJsonPath('sections.attachments.0.file_name', 'photo.jpg')
            ->assertJsonMissingPath('sections.activities.0.actor_email')
            ->assertJsonMissingPath('sections.attachments.0.object_key');
        $this->assertSame($count, DB::table('crm_legacy_records')->count());
        $this->assertDatabaseCount('crm_legacy_import_batches', 1);
    }

    public function test_missing_lead_returns_404_and_guest_cannot_read_details(): void
    {
        $this->get('/crm/admin/pipeline/lead-1')->assertRedirect('/crm/login');
        $this->actingAs(User::factory()->create())->getJson('/crm/admin/pipeline/lead-1')->assertNotFound();
    }

    public function test_malformed_related_record_returns_generic_error_without_raw_payload(): void
    {
        $batch = $this->batch();
        $this->record($batch, 'leads', 'lead-1', ['site_name' => 'Site One']);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'activities', 'source_id' => 'broken',
            'payload' => '{secret-broken-payload', 'payload_sha256' => str_repeat('c', 64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs(User::factory()->create())->getJson('/crm/admin/pipeline/lead-1');

        $response->assertStatus(500)->assertJsonPath('error', 'ไม่สามารถโหลดรายละเอียดได้');
        $this->assertStringNotContainsString('secret-broken-payload', $response->getContent());
        $this->assertStringNotContainsString('Syntax error', $response->getContent());
    }

    public function test_directory_has_five_ordered_accessible_detail_tabs_with_new_entry_actions(): void
    {
        $batch = $this->batch();
        $this->record($batch, 'leads', 'lead-1', ['site_name' => 'Site One']);

        $html = $this->actingAs(User::factory()->create())->get('/crm/admin/pipeline')->assertOk()->getContent();
        $labels = ['ข้อมูลลูกค้า', 'ธุรกิจและสัญญา', 'เทคนิคและส่งมอบ', 'ติดตามและเข้าพบ', 'ไฟล์และภาพหน้างาน'];
        $position = -1;
        foreach ($labels as $label) {
            $next = strpos($html, $label, $position + 1);
            $this->assertNotFalse($next, "Missing or misordered tab: {$label}");
            $position = $next;
        }
        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('role="tabpanel"', $html);
        $this->assertStringContainsString('ผู้รับผิดชอบปัจจุบัน', $html);
        $this->assertStringContainsString('บันทึกและไฟล์ใหม่จัดเก็บแยกใน CRM', $html);
        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringContainsString('อัปโหลดและจัดเก็บ', $html);
        $this->assertStringContainsString('เพิ่มบันทึก', $html);
        $this->assertStringContainsString('เพิ่มบันทึกและไฟล์ใหม่ได้', $html);
        $this->assertStringContainsString('ข้อมูลลูกค้าและผู้ติดต่อ', $html);
        $this->assertStringContainsString('บันทึกการแก้ไขทั้งหมด', $html);
        $this->assertStringContainsString('ข้อมูลปัจจุบันจาก CRM ใหม่', $html);
    }

    public function test_related_tabs_keep_engineering_stage_separate_and_order_events_newest_first(): void
    {
        $batch = $this->batch();
        $this->record($batch, 'leads', 'lead-1', ['site_name' => 'Site One', 'stage' => 'MOU']);
        $this->record($batch, 'projects', 'project-1', [
            'lead_id' => 'lead-1', 'project_code' => 'ENG-1', 'engineering_stage' => 'SURVEY',
        ]);
        $this->record($batch, 'activities', 'activity-old', [
            'lead_id' => 'lead-1', 'summary' => 'Earlier call', 'occurred_at' => '2026-10-15T10:00:00Z',
        ]);
        $this->record($batch, 'activities', 'activity-new', [
            'lead_id' => 'lead-1', 'summary' => 'Later call', 'occurred_at' => '2026-10-16T10:00:00Z',
        ]);
        $this->record($batch, 'visit_logs', 'visit-1', [
            'lead_id' => 'lead-1', 'summary' => 'Site visit', 'next_steps' => 'Check meter',
        ]);

        $this->actingAs(User::factory()->create())->getJson('/crm/admin/pipeline/lead-1')
            ->assertOk()
            ->assertJsonPath('opportunity.stage_code', 'MOU')
            ->assertJsonPath('sections.technical.project.engineering_stage', 'SURVEY')
            ->assertJsonPath('sections.activities.0.summary', 'Later call')
            ->assertJsonPath('sections.activities.1.summary', 'Earlier call')
            ->assertJsonPath('sections.visits.0.next_steps', 'Check meter')
            ->assertJsonCount(0, 'sections.attachments');
    }

    public function test_related_panels_explain_empty_records_and_offer_upload(): void
    {
        $batch = $this->batch();
        $this->record($batch, 'leads', 'lead-1', ['site_name' => 'Site One']);

        $html = $this->actingAs(User::factory()->create())->get('/crm/admin/pipeline')->assertOk()->getContent();

        $this->assertStringContainsString('ข้อมูลวิศวกรรม', $html);
        $this->assertStringContainsString('ประวัติการติดต่อ', $html);
        $this->assertStringContainsString('การเข้าพบ', $html);
        $this->assertStringContainsString('ยังไม่มีไฟล์ที่อัปโหลด', $html);
        $this->assertStringContainsString('อัปโหลดและจัดเก็บ', $html);
    }

    private function batch(): int
    {
        return DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('d', 64), 'source_name' => 'fixture.json',
            'exported_at' => '2026-10-16T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function record(int $batch, string $table, string $id, array $fields): void
    {
        $payload = json_encode(array_merge(['id' => $id], $fields), JSON_THROW_ON_ERROR);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => $table, 'source_id' => $id,
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
