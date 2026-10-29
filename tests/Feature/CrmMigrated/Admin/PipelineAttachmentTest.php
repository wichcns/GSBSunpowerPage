<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;
use ZipArchive;

class PipelineAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_is_private_and_visible_on_detail(): void
    {
        Storage::fake('local');
        $batchId = $this->lead('lead-1');
        $otherPayload = json_encode(['id' => 'lead-2', 'site_name' => 'Other site', 'stage' => 'NEW']);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batchId, 'source_table' => 'leads', 'source_id' => 'lead-2',
            'payload' => $otherPayload, 'payload_sha256' => hash('sha256', $otherPayload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/crm/admin/pipeline/lead-1/attachments', [
            'category' => 'SITE_PHOTO', 'note' => 'Front view',
            'file' => UploadedFile::fake()->image('site.jpg'),
        ])->assertCreated();

        $id = $response->json('file.id');
        $path = DB::table('crm_attachments')->where('id', $id)->value('storage_path');
        Storage::disk('local')->assertExists($path);
        $this->getJson('/crm/admin/pipeline/lead-1')
            ->assertJsonPath('sections.attachments.0.origin', 'current')
            ->assertJsonPath('sections.attachments.0.uploaded_by', $user->name);
        $this->get('/crm/admin/pipeline/lead-1/attachments/'.$id)->assertOk();
        $this->get('/crm/admin/pipeline/lead-2/attachments/'.$id)->assertNotFound();
        auth()->logout();
        $this->get('/crm/admin/pipeline/lead-1/attachments/'.$id)->assertRedirect('/crm/login');
    }

    public function test_disallowed_and_oversized_files_do_not_create_records(): void
    {
        Storage::fake('local');
        $this->lead('lead-1');
        $this->actingAs(User::factory()->create())->postJson('/crm/admin/pipeline/lead-1/attachments', [
            'category' => 'SITE_PHOTO', 'file' => UploadedFile::fake()->createWithContent('danger.pdf', 'not a pdf'),
        ])->assertUnprocessable();
        $this->postJson('/crm/admin/pipeline/lead-1/attachments', [
            'category' => 'SITE_PHOTO', 'file' => UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf'),
        ])->assertUnprocessable();
        $this->assertDatabaseCount('crm_attachments', 0);
    }

    public function test_valid_office_document_can_be_uploaded(): void
    {
        Storage::fake('local');
        $this->lead('lead-1');
        $path = tempnam(sys_get_temp_dir(), 'crm-office-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<Types/>');
        $zip->addFromString('word/document.xml', '<document/>');
        $zip->close();
        try {
            $this->actingAs(User::factory()->create())->postJson('/crm/admin/pipeline/lead-1/attachments', [
                'category' => 'COMPANY_DOC',
                'file' => new UploadedFile($path, 'sample.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
            ])->assertCreated();
        } finally {
            unlink($path);
        }
    }

    public function test_metadata_insert_failure_removes_new_file(): void
    {
        Storage::fake('local');
        $this->lead('lead-1');
        DB::statement("CREATE TRIGGER block_attachment BEFORE INSERT ON crm_attachments BEGIN SELECT RAISE(ABORT, 'blocked'); END");
        $this->actingAs(User::factory()->create())->postJson('/crm/admin/pipeline/lead-1/attachments', [
            'category' => 'SITE_PHOTO', 'file' => UploadedFile::fake()->image('site.jpg'),
        ])->assertStatus(500);
        $this->assertSame([], Storage::disk('local')->allFiles('crm'));
    }

    public function test_uploaded_file_remains_accessible_after_new_import_omits_lead(): void
    {
        Storage::fake('local');
        $this->lead('lead-1');
        $user = User::factory()->create();
        $fileId = $this->actingAs($user)->postJson('/crm/admin/pipeline/lead-1/attachments', [
            'category' => 'SITE_PHOTO', 'file' => UploadedFile::fake()->image('old-site.jpg'),
        ])->assertCreated()->json('file.id');
        DB::table('crm_legacy_import_batches')->insert([
            'source_sha256' => str_repeat('e', 64), 'source_name' => 'new-export.json',
            'exported_at' => '2026-10-19T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->get('/crm/admin/pipeline/lead-1/attachments/'.$fileId)->assertOk();
    }

    private function lead(string $sourceId): int
    {
        $batchId = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('d', 64), 'source_name' => 'fixture.json',
            'exported_at' => '2026-10-16T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $payload = json_encode(['id' => $sourceId, 'site_name' => 'Test site', 'stage' => 'NEW']);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batchId, 'source_table' => 'leads', 'source_id' => $sourceId,
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $batchId;
    }
}
