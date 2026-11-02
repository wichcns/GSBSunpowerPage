<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class ContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_directory_only_contains_three_approved_sales_stages(): void
    {
        $batch = $this->batch();
        foreach (['INTERNAL_APPROVED', 'MOU_SIGNED', 'CONTRACT_SIGNED', 'NEW'] as $stage) {
            $this->lead($batch, strtolower($stage), ['site_name' => $stage, 'stage' => $stage, 'contract_status' => 'DRAFT']);
        }
        $this->actingAs(User::factory()->create())->get('/crm/admin/contracts')->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 3);
    }

    public function test_missing_contract_field_names_follow_the_selected_language(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'missing-terms', ['site_name' => 'Missing Terms Site', 'stage' => 'INTERNAL_APPROVED']);

        $this->actingAs(User::factory()->create(['locale' => 'zh']))
            ->get('/crm/admin/contracts')->assertOk()
            ->assertSee('合同状态')->assertSee('合同期限')->assertSee('到期日')
            ->assertDontSee('สถานะสัญญา')->assertDontSee('อายุสัญญา')->assertDontSee('วันหมดอายุ');
    }

    public function test_contract_key_terms_save_without_losing_existing_values(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'terms', ['site_name' => 'Terms site', 'stage' => 'CONTRACT_SIGNED', 'contract_status' => 'SIGNED']);
        $this->actingAs(User::factory()->create())->patch('/crm/admin/contracts/terms/terms', [
            'version' => 0, 'parking_spaces' => 6, 'commercial_model' => 'RENT', 'contract_term' => '10 years',
            'contract_signed_at' => '2026-10-30', 'contract_effective_at' => '2026-11-01',
            'rent_free_months' => 2, 'monthly_payment_day' => 5,
        ])->assertRedirect();
        $values = json_decode(DB::table('crm_business_terms')->where('lead_source_id', 'terms')->value('values'), true);
        $this->assertSame('SIGNED', $values['contract_status']);
        $this->assertSame(5, $values['monthly_payment_day']);
        $this->get('/crm/admin/contracts')->assertOk()->assertSee('2026-11-01')->assertSee('10 years');
    }

    public function test_contract_terms_reject_ineligible_lead_invalid_day_and_stale_version(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'new', ['stage' => 'NEW']);
        $this->lead($batch, 'eligible', ['stage' => 'MOU_SIGNED']);
        $this->actingAs(User::factory()->create());
        $this->patch('/crm/admin/contracts/new/terms', ['version' => 0])->assertNotFound();
        $this->patch('/crm/admin/contracts/eligible/terms', ['version' => 0, 'monthly_payment_day' => 32])
            ->assertSessionHasErrors('monthly_payment_day');
        $this->patch('/crm/admin/contracts/eligible/terms', ['version' => 0, 'commercial_model' => 'PARKING_RENT'])
            ->assertRedirect();
        $this->patch('/crm/admin/contracts/eligible/terms', ['version' => 0, 'commercial_model' => 'AREA_RENT'])
            ->assertStatus(409);
        $this->assertDatabaseHas('crm_business_terms', ['lead_source_id' => 'eligible', 'version' => 1]);
        $this->assertDatabaseCount('crm_business_terms_revisions', 1);
    }

    public function test_guest_is_redirected_and_authenticated_user_sees_contract_directory(): void
    {
        $this->get('/crm/admin/contracts')->assertRedirect('/crm/login');
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', ['site_name' => 'Bangchak', 'stage' => 'INTERNAL_APPROVED', 'assigned_to' => 'Kira', 'contract_status' => 'DRAFT', 'contract_term' => '3 years']);
        $this->lead($batch, 'lead-3', ['site_name' => 'Stage only', 'stage' => 'CONTRACT_SIGNED']);
        $this->lead($batch, 'lead-2', ['site_name' => 'No contract']);

        $this->actingAs(User::factory()->create())->get('/crm/admin/contracts')
            ->assertOk()->assertSee('Bangchak')->assertSee('DRAFT')->assertSee('Kira')
            ->assertSee('Stage only')->assertSee('ยังไม่มีรายละเอียดสัญญา')->assertDontSee('No contract')
            ->assertSee('role="dialog"', false)->assertSee('contract-detail-title', false)
            ->assertDontSee('pipeline-panel-business', false);
    }

    public function test_contract_detail_uses_effective_values_and_only_contract_files(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', [
            'site_name' => 'Contract site', 'stage' => 'CONTRACT_SIGNED',
            'contract_status' => 'DRAFT', 'contract_term' => '3 years',
        ]);
        $this->lead($batch, 'lead-2', ['site_name' => 'Other site', 'stage' => 'MOU']);
        $editor = User::factory()->create();
        DB::table('crm_business_terms')->insert([
            'lead_source_id' => 'lead-1', 'values' => json_encode(['contract_status' => '0'], JSON_THROW_ON_ERROR),
            'version' => 1, 'updated_by_user_id' => $editor->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get('/crm/admin/contracts/lead-1')->assertRedirect('/crm/login');
        $this->actingAs($editor)->getJson('/crm/admin/contracts/lead-1')->assertOk()
            ->assertJsonPath('opportunity.site_name', 'Contract site')
            ->assertJsonPath('business.contract_status', '0')
            ->assertJsonPath('business.contract_term', '3 years')
            ->assertJsonPath('business._version', 1)
            ->assertJsonCount(0, 'attachments');
        $this->getJson('/crm/admin/contracts/lead-2')->assertNotFound();
        $this->assertDatabaseCount('crm_business_terms', 1);
    }

    public function test_search_and_filters_select_effective_contract_data(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', ['site_name' => 'Alpha', 'area' => 'Bangkok', 'stage' => 'MOU_SIGNED', 'contract_status' => 'DRAFT']);
        $this->lead($batch, 'lead-2', ['site_name' => 'Beta', 'stage' => 'CONTRACT_SIGNED', 'contract_status' => 'SIGNED']);
        $this->actingAs(User::factory()->create());

        $this->get('/crm/admin/contracts?q=Alpha&stage=MOU_SIGNED&contract_status=DRAFT')
            ->assertOk()->assertSee('Alpha')->assertDontSee('Beta');
        $this->get('/crm/admin/contracts?expiry=invalid')->assertOk();
        $this->get('/crm/admin/contracts?q[]=bad&stage[]=SIGNED')->assertOk();
    }

    public function test_contract_directory_keeps_explicit_zero_codes_visible(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', [
            'site_name' => 'Zero code site', 'stage' => 'CONTRACT_SIGNED',
            'contract_status' => 'DRAFT', 'contract_term' => '3 years',
        ]);
        $editor = User::factory()->create();
        DB::table('crm_business_terms')->insert([
            'lead_source_id' => 'lead-1',
            'values' => json_encode(['contract_status' => '0', 'contract_term' => '0'], JSON_THROW_ON_ERROR),
            'version' => 1, 'updated_by_user_id' => $editor->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($editor)->get('/crm/admin/contracts')->assertOk()
            ->assertSee('สถานะสัญญา</p><p class="text-sm">0</p>', false)
            ->assertSee('อายุสัญญา</p><p class="text-sm">0</p>', false)
            ->assertDontSee('DRAFT')->assertDontSee('3 years');
    }

    public function test_site_edit_is_reflected_in_pipeline_and_contracts_without_changing_import(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', [
            'site_name' => 'Imported site', 'area' => 'Old area', 'stage' => 'CONTRACT_SIGNED',
        ]);
        $originalPayload = DB::table('crm_legacy_records')->where('source_id', 'lead-1')->value('payload');
        $site = Site::create([
            'name' => 'Current site', 'area' => 'Current area', 'legacy_lead_id' => 'lead-1',
        ]);
        $this->actingAs(User::factory()->create());
        $fingerprint = $this->getJson(route('crm.admin.sites.show', $site))->json('site.edit_fingerprint');

        $this->patch(route('crm.admin.sites.update', $site), [
            'name' => 'Renamed current site', 'area' => 'Updated area', 'site_fingerprint' => $fingerprint,
        ])->assertRedirect();

        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.profile.site_name', 'Renamed current site')
            ->assertJsonPath('sections.profile.area', 'Updated area');
        $this->get('/crm/admin/pipeline?q=Renamed%20current%20site')->assertOk()
            ->assertSee('Renamed current site')->assertDontSee('Imported site');
        $this->get('/crm/admin/contracts?q=Renamed%20current%20site')->assertOk()
            ->assertSee('Renamed current site')->assertDontSee('Imported site');
        $this->assertSame($originalPayload, DB::table('crm_legacy_records')->where('source_id', 'lead-1')->value('payload'));
    }

    public function test_sites_shows_imported_contact_when_no_current_contact_exists(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', [
            'site_name' => 'Imported site', 'contact_name' => 'Khun Legacy',
        ]);
        $site = Site::create([
            'name' => 'Current site', 'legacy_lead_id' => 'lead-1',
        ]);
        $this->actingAs(User::factory()->create());

        $this->get('/crm/admin/sites')->assertOk()->assertSee('Khun Legacy');
        $this->getJson(route('crm.admin.sites.show', $site))->assertOk()
            ->assertJsonPath('site.contact_preview.name', 'Khun Legacy');
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.profile.contact_name', 'Khun Legacy');
        $this->assertDatabaseCount('crm_contacts', 0);
    }

    public function test_new_import_keeps_current_contact_but_refreshes_unedited_contact_preview(): void
    {
        $first = $this->batch();
        $this->lead($first, 'lead-1', [
            'site_name' => 'First imported site', 'contact_name' => 'First imported contact', 'stage' => 'CONTRACT_SIGNED',
        ]);
        $this->lead($first, 'lead-2', [
            'site_name' => 'Other imported site', 'contact_name' => 'Old fallback contact', 'stage' => 'CONTRACT_SIGNED',
        ]);
        $originalPayload = DB::table('crm_legacy_records')->where('batch_id', $first)
            ->where('source_id', 'lead-1')->value('payload');
        $edited = Site::create(['name' => 'Team-edited site', 'legacy_lead_id' => 'lead-1']);
        $edited->contacts()->create(['name' => 'Team-edited contact', 'is_primary' => true]);
        Site::create(['name' => 'Unedited contact site', 'legacy_lead_id' => 'lead-2']);

        $latest = $this->batch('b');
        $this->lead($latest, 'lead-1', [
            'site_name' => 'Second imported site', 'contact_name' => 'Second imported contact', 'stage' => 'CONTRACT_SIGNED',
        ]);
        $this->lead($latest, 'lead-2', [
            'site_name' => 'Other imported site', 'contact_name' => 'New fallback contact', 'stage' => 'CONTRACT_SIGNED',
        ]);
        $this->actingAs(User::factory()->create());

        $this->get('/crm/admin/sites')->assertOk()
            ->assertSee('Team-edited contact')->assertSee('New fallback contact')
            ->assertDontSee('Old fallback contact')->assertDontSee('Second imported contact');
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.profile.site_name', 'Team-edited site')
            ->assertJsonPath('sections.profile.contact_name', 'Team-edited contact');
        $this->getJson('/crm/admin/pipeline/lead-2')->assertOk()
            ->assertJsonPath('sections.profile.contact_name', 'New fallback contact');
        $this->get('/crm/admin/contracts')->assertOk()->assertSee('Team-edited site')
            ->assertDontSee('Second imported site');
        $this->assertSame($originalPayload, DB::table('crm_legacy_records')->where('batch_id', $first)
            ->where('source_id', 'lead-1')->value('payload'));
        $this->assertDatabaseCount('crm_contacts', 1);
    }

    public function test_document_and_missing_information_filters_explain_the_available_evidence(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'missing', ['site_name' => 'Missing site', 'stage' => 'CONTRACT_SIGNED']);
        $this->lead($batch, 'legacy', ['site_name' => 'Legacy site', 'stage' => 'CONTRACT_SIGNED',
            'contract_status' => 'DRAFT', 'contract_term' => '3 years', 'contract_expiry' => '2027-10-19']);
        $this->lead($batch, 'uploaded', ['site_name' => 'Uploaded site', 'stage' => 'CONTRACT_SIGNED',
            'contract_status' => 'SIGNED', 'contract_term' => '5 years', 'contract_expiry' => '2028-10-19']);
        $payload = json_encode(['id' => 'file-1', 'lead_id' => 'legacy', 'category' => 'CONTRACT', 'file_name' => 'old.pdf']);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'attachments', 'source_id' => 'file-1',
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $user = User::factory()->create();
        DB::table('crm_attachments')->insert([
            'lead_source_id' => 'uploaded', 'category' => 'CONTRACT', 'original_name' => 'new.pdf',
            'storage_path' => 'crm/test/new.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 100,
            'uploaded_by_user_id' => $user->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get('/crm/admin/contracts')->assertOk()
            ->assertSee('ยังไม่พบไฟล์ในข้อมูลที่มี')
            ->assertSee('ไม่ได้ยืนยันว่าไม่มีเอกสารอยู่ภายนอกระบบ')
            ->assertSee('พบข้อมูลไฟล์เดิม')
            ->assertSee('มีไฟล์ใน CRM ใหม่')
            ->assertSee('ขาด: สถานะสัญญา, อายุสัญญา, วันหมดอายุ');
        $this->get('/crm/admin/contracts?document=not_found')->assertOk()
            ->assertSee('Missing site')->assertDontSee('Legacy site')->assertDontSee('Uploaded site');
        $this->get('/crm/admin/contracts?document=legacy_metadata')->assertOk()
            ->assertSee('Legacy site')->assertDontSee('Missing site');
        $this->get('/crm/admin/contracts?information=missing')->assertOk()
            ->assertSee('Missing site')->assertDontSee('Uploaded site');
    }

    private function batch(string $hashCharacter = 'a'): int
    {
        return DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat($hashCharacter, 64), 'source_name' => 'fixture.json',
            'exported_at' => '2026-10-16T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function lead(int $batch, string $id, array $fields): void
    {
        $payload = json_encode(array_merge(['id' => $id, 'site_name' => $id, 'stage' => 'NEW'], $fields));
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $id,
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
