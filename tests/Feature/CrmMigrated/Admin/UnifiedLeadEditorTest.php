<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class UnifiedLeadEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_save_updates_profile_business_and_technical_without_changing_import(): void
    {
        $original = $this->lead();
        $editor = User::factory()->create();
        $this->actingAs($editor)->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['site_name' => 'Updated site', 'next_action' => 'Call owner'],
            'business' => ['rental_cost' => 0],
            'technical' => ['requested_chargers' => 2],
        ]))->assertOk()->assertJsonPath('version', 1);

        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.profile.site_name', 'Updated site')
            ->assertJsonPath('sections.profile.next_action', 'Call owner')
            ->assertJsonPath('sections.profile._updated_by', $editor->name)
            ->assertJsonPath('sections.business.rental_cost', 0)
            ->assertJsonPath('sections.technical.requested_chargers', 2)
            ->assertJsonPath('sections.technical._updated_by', $editor->name);
        $this->assertSame($original, DB::table('crm_legacy_records')->where('source_id', 'lead-1')->value('payload'));
        $this->assertDatabaseHas('crm_sites', ['legacy_lead_id' => 'lead-1', 'name' => 'Updated site']);
        $this->assertDatabaseCount('crm_lead_detail_revisions', 1);
        $this->get('/crm/admin/pipeline')->assertOk()->assertSee('Call owner');
    }

    public function test_invalid_section_saves_nothing_and_stale_version_is_rejected(): void
    {
        $this->lead();
        $this->actingAs(User::factory()->create());
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['site_name' => 'Should not save'],
            'technical' => ['requested_chargers' => -1],
        ]))->assertUnprocessable();
        $this->assertDatabaseCount('crm_lead_details', 0);

        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['site_name' => 'First change'],
        ]))->assertOk();
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['site_name' => 'Stale change'],
        ]))->assertStatus(409);
        $this->getJson('/crm/admin/pipeline/lead-1')->assertJsonPath('sections.profile.site_name', 'First change');
    }

    public function test_visit_updates_shared_next_action_after_editor_save(): void
    {
        $this->lead();
        $this->actingAs(User::factory()->create());
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['next_action' => 'Prepare quote'],
        ]))->assertOk();
        $this->postJson('/crm/admin/pipeline/lead-1/visits', [
            'visit_type' => 'FOLLOW_UP', 'occurred_at' => '2026-10-20T10:00',
            'internal_participants' => 'GSB', 'summary' => 'Called', 'next_steps' => 'Send quote',
        ])->assertCreated();
        $this->getJson('/crm/admin/pipeline/lead-1')->assertJsonPath('sections.profile.next_action', 'Send quote');
    }

    public function test_guest_cannot_save(): void
    {
        $this->patchJson('/crm/admin/pipeline/lead-1/details', [])->assertUnauthorized();
    }

    public function test_clearing_current_next_action_date_does_not_restore_imported_date(): void
    {
        $this->lead(['next_action_at' => '2026-11-01T09:00:00Z']);
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['next_action_at' => null],
        ]))->assertOk();
        $this->getJson('/crm/admin/pipeline/lead-1')->assertJsonPath('sections.profile.next_action_at', null)
            ->assertJsonPath('opportunity.next_action_at_raw', '');
    }

    public function test_existing_legacy_investment_code_can_be_preserved_when_editing_another_field(): void
    {
        $this->lead(['investment_type' => 'sharing']);
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['site_name' => 'Updated name'],
            'business' => ['investment_type' => 'sharing', 'rental_cost' => 1000],
        ]))->assertOk();
        $this->getJson('/crm/admin/pipeline/lead-1')->assertJsonPath('sections.business.investment_type', 'sharing');
    }

    public function test_existing_site_master_and_primary_contact_are_shown_as_current_values(): void
    {
        $this->lead();
        $siteId = DB::table('crm_sites')->insertGetId([
            'site_code' => 'SITE-000001', 'name' => 'Current site', 'area' => 'Bangkok',
            'legacy_lead_id' => 'lead-1', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_contacts')->insert([
            'site_id' => $siteId, 'name' => 'Current contact', 'is_primary' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create())->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.profile.site_name', 'Current site')
            ->assertJsonPath('sections.profile.contact_name', 'Current contact');
    }

    public function test_saving_unchanged_current_site_does_not_create_a_revision(): void
    {
        $this->lead();
        DB::table('crm_sites')->insert([
            'site_code' => 'SITE-000001', 'name' => 'Current site', 'legacy_lead_id' => 'lead-1',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create());
        $fingerprint = $this->getJson('/crm/admin/pipeline/lead-1')->json('sections.profile.current_site.fingerprint');
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'site_fingerprint' => $fingerprint, 'profile' => ['site_name' => 'Current site'],
            'business' => ['rental_cost' => 1000],
        ]))->assertOk()->assertJsonPath('changed', false);
        $this->assertDatabaseCount('crm_lead_detail_revisions', 0);
        $this->assertDatabaseCount('crm_site_audits', 0);
    }

    public function test_contact_cannot_have_a_phone_without_a_name(): void
    {
        $this->lead();
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'profile' => ['contact_name' => null, 'phone' => '0812345678'],
        ]))->assertUnprocessable()->assertJsonValidationErrors('profile.contact_name');
        $this->assertDatabaseCount('crm_lead_details', 0);
    }

    public function test_stale_site_master_fingerprint_rejects_overwriting_a_sites_edit(): void
    {
        $this->lead();
        $siteId = DB::table('crm_sites')->insertGetId([
            'site_code' => 'SITE-000001', 'name' => 'Before', 'legacy_lead_id' => 'lead-1',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create());
        $fingerprint = $this->getJson('/crm/admin/pipeline/lead-1')->json('sections.profile.current_site.fingerprint');
        DB::table('crm_sites')->where('id', $siteId)->update(['name' => 'Newer Sites edit']);
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'site_fingerprint' => $fingerprint, 'profile' => ['site_name' => 'Stale editor'],
            'business' => ['rental_cost' => 1000],
        ]))->assertStatus(409);
        $this->assertDatabaseHas('crm_sites', ['id' => $siteId, 'name' => 'Newer Sites edit']);
    }

    public function test_stale_sites_form_cannot_overwrite_a_newer_unified_editor_save(): void
    {
        $this->lead();
        $siteId = DB::table('crm_sites')->insertGetId([
            'site_code' => 'SITE-000001', 'name' => 'Original site', 'legacy_lead_id' => 'lead-1',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create());
        $siteFormFingerprint = $this->getJson(route('crm.admin.sites.show', $siteId))
            ->assertOk()->json('site.edit_fingerprint');
        $leadFingerprint = $this->getJson('/crm/admin/pipeline/lead-1')
            ->assertOk()->json('sections.profile.current_site.fingerprint');

        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'site_fingerprint' => $leadFingerprint,
            'profile' => ['site_name' => 'Newer Pipeline edit'],
        ]))->assertOk();

        $this->patch(route('crm.admin.sites.update', $siteId), [
            'name' => 'Stale Sites edit', 'site_fingerprint' => $siteFormFingerprint,
        ])->assertSessionHasErrors('site_fingerprint');
        $this->assertDatabaseHas('crm_sites', ['id' => $siteId, 'name' => 'Newer Pipeline edit']);
    }

    public function test_pipeline_site_edit_appears_in_sites_history_with_the_editor(): void
    {
        $this->lead();
        $siteId = DB::table('crm_sites')->insertGetId([
            'site_code' => 'SITE-000001', 'name' => 'Original site', 'legacy_lead_id' => 'lead-1',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $editor = User::factory()->create(['name' => 'Pipeline Editor']);
        $this->actingAs($editor);
        $fingerprint = $this->getJson('/crm/admin/pipeline/lead-1')->json('sections.profile.current_site.fingerprint');

        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'site_fingerprint' => $fingerprint, 'profile' => ['site_name' => 'Updated from Pipeline'],
        ]))->assertOk();

        $this->getJson(route('crm.admin.sites.show', $siteId))->assertOk()
            ->assertJsonPath('site.audits.0.action', 'updated')
            ->assertJsonPath('site.audits.0.actor.name', 'Pipeline Editor');
    }

    public function test_one_pipeline_save_keeps_sites_contacts_and_contracts_in_sync(): void
    {
        $original = $this->lead([
            'stage' => 'CONTRACT', 'contact_name' => 'Imported contact',
            'contract_status' => 'DRAFT', 'contract_term' => '3 years',
        ]);
        $siteId = DB::table('crm_sites')->insertGetId([
            'site_code' => 'SITE-000001', 'name' => 'Original site', 'legacy_lead_id' => 'lead-1',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_contacts')->insert([
            ['site_id' => $siteId, 'name' => 'Current primary', 'is_primary' => true,
                'created_at' => now(), 'updated_at' => now()],
            ['site_id' => $siteId, 'name' => 'Other contact', 'is_primary' => false,
                'created_at' => now(), 'updated_at' => now()],
        ]);
        $this->actingAs(User::factory()->create());
        $fingerprint = $this->getJson('/crm/admin/pipeline/lead-1')
            ->assertOk()->json('sections.profile.current_site.fingerprint');

        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'site_fingerprint' => $fingerprint,
            'profile' => ['site_name' => 'Pipeline renamed site', 'contact_name' => 'Updated primary'],
            'business' => ['contract_status' => 'SIGNED', 'contract_term' => '5 years'],
        ]))->assertOk();

        $this->getJson(route('crm.admin.sites.show', $siteId))->assertOk()
            ->assertJsonPath('site.name', 'Pipeline renamed site');
        $this->assertDatabaseHas('crm_contacts', ['site_id' => $siteId, 'name' => 'Updated primary', 'is_primary' => true]);
        $this->assertDatabaseHas('crm_contacts', ['site_id' => $siteId, 'name' => 'Other contact', 'is_primary' => false]);
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.profile.site_name', 'Pipeline renamed site')
            ->assertJsonPath('sections.profile.contact_name', 'Updated primary')
            ->assertJsonPath('sections.business.contract_status', 'SIGNED');
        $this->get('/crm/admin/contracts?q=Pipeline%20renamed%20site')->assertOk()
            ->assertSee('Pipeline renamed site')->assertSee('SIGNED')->assertSee('5 years')
            ->assertDontSee('DRAFT')->assertDontSee('3 years');
        $this->assertSame($original, DB::table('crm_legacy_records')->where('source_id', 'lead-1')->value('payload'));
    }

    public function test_unified_editor_cannot_overwrite_a_newer_business_only_save(): void
    {
        $this->lead(['rental_cost' => 1000]);
        $this->actingAs(User::factory()->create());
        $olderEditor = $this->payload([
            'profile' => ['site_name' => 'Stale name'],
            'business' => ['rental_cost' => 2000],
        ]);
        $currentTerms = array_fill_keys(\App\Crm\Http\Requests\SaveCrmBusinessTermsRequest::FIELDS, null);
        $currentTerms['rental_cost'] = 1500;

        $this->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null, 'values' => $currentTerms,
        ])->assertOk();
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $olderEditor)->assertStatus(409);

        $this->assertDatabaseCount('crm_lead_detail_revisions', 0);
        $this->assertDatabaseCount('crm_site_audits', 0);
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.business.rental_cost', 1500)
            ->assertJsonPath('sections.profile.site_name', 'Original site');
    }

    public function test_assigned_owner_cannot_be_silently_replaced_by_free_text(): void
    {
        $this->lead();
        $owner = User::factory()->create(['name' => 'Current Owner']);
        DB::table('crm_sites')->insert([
            'site_code' => 'SITE-000001', 'name' => 'Original site', 'legacy_lead_id' => 'lead-1',
            'owner_user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs(User::factory()->create());
        $fingerprint = $this->getJson('/crm/admin/pipeline/lead-1')->json('sections.profile.current_site.fingerprint');
        $this->patchJson('/crm/admin/pipeline/lead-1/details', $this->payload([
            'site_fingerprint' => $fingerprint, 'profile' => ['assigned_to' => 'Someone else'],
            'business' => ['rental_cost' => 1000],
        ]))->assertUnprocessable()->assertJsonValidationErrors('profile.assigned_to');
    }

    private function lead(array $fields = []): string
    {
        $batchId = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'fixture.json',
            'exported_at' => '2026-10-16T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $payload = json_encode(array_replace(['id' => 'lead-1', 'site_name' => 'Original site', 'stage' => 'NEW', 'rental_cost' => 1000], $fields), JSON_THROW_ON_ERROR);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batchId, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $payload;
    }

    private function payload(array $changes = []): array
    {
        return array_replace_recursive([
            'version' => null,
            'site_fingerprint' => null,
            'profile' => ['site_name' => 'Original site', 'area' => null, 'location_type' => null,
                'lead_source' => null, 'map_url' => null, 'assigned_to' => null, 'contact_name' => null,
                'contact_role' => null, 'phone' => null, 'line_name' => null, 'email' => null,
                'appointment_status' => null, 'next_action' => null, 'next_action_at' => null],
            'business' => array_fill_keys([
                'commercial_model', 'investment_type', 'rental_cost', 'rental_terms', 'rental_unit',
                'revenue_share_amount', 'revenue_share_unit', 'parking_spaces', 'deposit_amount',
                'deposit_months', 'gsb_investment_percent', 'partner_investment_percent',
                'contract_status', 'contract_term', 'contract_expiry', 'rent_free_months', 'notes',
            ], null),
            'technical' => array_fill_keys([
                'power_authority', 'meter_status', 'transformer_owner', 'transformer_kva',
                'transformer_cost', 'requested_chargers', 'requested_connectors', 'charger_power_kw',
                'installation_status', 'low_priority_status',
            ], null),
        ], $changes);
    }
}
