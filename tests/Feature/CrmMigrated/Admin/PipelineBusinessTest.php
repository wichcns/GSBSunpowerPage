<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class PipelineBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_terms_have_separate_snapshot_and_revision_tables(): void
    {
        $this->assertTrue(Schema::hasTable('crm_business_terms'));
        $this->assertTrue(Schema::hasTable('crm_business_terms_revisions'));
    }

    public function test_saving_terms_keeps_import_immutable_and_records_editor(): void
    {
        $original = $this->lead('lead-1', ['rental_cost' => 1000, 'contract_status' => 'DRAFT']);
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null,
            'values' => $this->values(['rental_cost' => 1500, 'contract_status' => 'SIGNED']),
        ])->assertOk()->assertJsonPath('version', 1);

        $this->assertSame($original, DB::table('crm_legacy_records')->where('source_table', 'leads')->where('source_id', 'lead-1')->value('payload'));
        $this->assertDatabaseHas('crm_business_terms', ['lead_source_id' => 'lead-1', 'version' => 1, 'updated_by_user_id' => $user->id]);
        $this->assertDatabaseHas('crm_business_terms_revisions', ['lead_source_id' => 'lead-1', 'version' => 1, 'changed_by_user_id' => $user->id]);
    }

    public function test_guest_missing_lead_and_unknown_field_are_rejected(): void
    {
        $this->patchJson('/crm/admin/pipeline/missing/business', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/pipeline/missing/business', [
            'version' => null, 'values' => $this->values(),
        ])->assertNotFound();
        $this->lead('lead-1');
        $this->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null, 'values' => $this->values(['admin' => true]),
        ])->assertUnprocessable();
    }

    public function test_invalid_numeric_and_joint_investment_values_are_rejected(): void
    {
        $this->lead('lead-1');
        $this->actingAs(User::factory()->create());
        foreach ([
            ['rental_cost' => -1], ['parking_spaces' => -1], ['deposit_amount' => -1],
            ['gsb_investment_percent' => 101], ['contract_expiry' => 'not-a-date'],
            ['investment_type' => 'JOINT', 'gsb_investment_percent' => 60, 'partner_investment_percent' => null],
            ['investment_type' => 'JOINT', 'gsb_investment_percent' => 60, 'partner_investment_percent' => 30],
        ] as $invalid) {
            $this->patchJson('/crm/admin/pipeline/lead-1/business', [
                'version' => null, 'values' => $this->values($invalid),
            ])->assertUnprocessable();
        }
        $this->assertDatabaseCount('crm_business_terms', 0);
    }

    public function test_stale_update_and_noop_do_not_add_revisions(): void
    {
        $this->lead('lead-1');
        $this->actingAs(User::factory()->create());
        $first = $this->values(['rental_cost' => 0]);
        $this->patchJson('/crm/admin/pipeline/lead-1/business', ['version' => null, 'values' => $first])->assertOk();
        $this->patchJson('/crm/admin/pipeline/lead-1/business', ['version' => 1, 'values' => $first])->assertOk()->assertJsonPath('version', 1);
        $this->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null, 'values' => $this->values(['rental_cost' => 500]),
        ])->assertStatus(409);
        $this->assertDatabaseCount('crm_business_terms_revisions', 1);
    }

    public function test_explicit_null_clears_current_value_and_does_not_restore_imported_value(): void
    {
        $this->lead('lead-1', ['rental_cost' => 1000]);
        $this->actingAs(User::factory()->create());
        $this->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null, 'values' => $this->values(['rental_cost' => 2000]),
        ])->assertOk();
        $this->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => 1, 'values' => $this->values(),
        ])->assertOk()->assertJsonPath('version', 2);
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.business.rental_cost', null)
            ->assertJsonPath('sections.business._legacy.rental_cost', 1000);
    }

    public function test_identical_first_save_does_not_create_revision(): void
    {
        $this->lead('lead-1', ['rental_cost' => 1000]);
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null, 'values' => $this->values(['rental_cost' => 1000]),
        ])->assertOk()->assertJsonPath('version', null);
        $this->assertDatabaseCount('crm_business_terms', 0);
        $this->assertDatabaseCount('crm_business_terms_revisions', 0);
    }

    public function test_current_terms_survive_new_import_and_do_not_change_other_leads(): void
    {
        $this->lead('lead-1', ['rental_cost' => 1000]);
        $this->actingAs(User::factory()->create())->patchJson('/crm/admin/pipeline/lead-1/business', [
            'version' => null, 'values' => $this->values(['rental_cost' => 1500]),
        ])->assertOk();
        $this->lead('lead-1', ['rental_cost' => 1200]);
        $latestBatch = DB::table('crm_legacy_import_batches')->latest('id')->value('id');
        $other = json_encode(['id' => 'lead-2', 'site_name' => 'Other', 'stage' => 'NEW', 'rental_cost' => 2000], JSON_THROW_ON_ERROR);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $latestBatch, 'source_table' => 'leads', 'source_id' => 'lead-2',
            'payload' => $other, 'payload_sha256' => hash('sha256', $other),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->getJson('/crm/admin/pipeline/lead-1')->assertOk()
            ->assertJsonPath('sections.business.rental_cost', 1500)
            ->assertJsonPath('sections.business._legacy.rental_cost', 1200);
        $this->getJson('/crm/admin/pipeline/lead-2')->assertOk()
            ->assertJsonPath('sections.business.rental_cost', 2000)
            ->assertJsonPath('sections.business._source', 'legacy');
    }

    private function lead(string $id, array $fields = []): string
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => hash('sha256', $id.json_encode($fields).DB::table('crm_legacy_import_batches')->count()), 'source_name' => 'fixture.json',
            'exported_at' => '2026-10-16T00:00:00Z', 'source_counts' => '{}',
            'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $payload = json_encode(['id' => $id, 'site_name' => 'Fixture', 'stage' => 'NEW'] + $fields, JSON_THROW_ON_ERROR);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $id,
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $payload;
    }

    private function values(array $changes = []): array
    {
        return array_replace(array_fill_keys([
            'commercial_model', 'investment_type', 'rental_cost', 'rental_terms', 'rental_unit',
            'revenue_share_amount', 'revenue_share_unit', 'parking_spaces', 'deposit_amount',
            'deposit_months', 'gsb_investment_percent', 'partner_investment_percent',
            'contract_status', 'contract_term', 'contract_expiry', 'rent_free_months', 'notes',
        ], null), $changes);
    }
}
