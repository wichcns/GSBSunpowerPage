<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class NativeOpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_site_and_contacts_appear_in_sales_pipeline(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('crm.admin.sites.store'), [
            'name' => 'New charging site',
            'contacts' => [['name' => 'Site partner', 'is_primary' => '1'], ['name' => 'Second partner', 'is_primary' => '0']],
        ])->assertRedirect();

        $site = Site::query()->firstOrFail();
        $this->assertNotNull($site->legacy_lead_id);
        $this->getJson(route('crm.admin.sites.show', $site))->assertOk()
            ->assertJsonPath('site.opportunity_create_url', null);

        $site->refresh();
        $this->assertNotNull($site->legacy_lead_id);
        $this->getJson(route('crm.admin.sites.show', $site))->assertOk()
            ->assertJsonPath('site.opportunity_create_url', null);
        $this->assertDatabaseHas('crm_native_opportunities', ['site_id' => $site->id, 'source_id' => $site->legacy_lead_id]);
        $this->get(route('crm.admin.pipeline.index'))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()['stage_code'] === 'NEW');
        $this->getJson(route('crm.admin.pipeline.show', $site->legacy_lead_id))->assertOk()
            ->assertJsonPath('opportunity.site_id', $site->id)
            ->assertJsonPath('sections.profile.contact_name', 'Site partner')
            ->assertJsonPath('sections.profile.contacts.0.name', 'Site partner')
            ->assertJsonPath('sections.profile.contacts.0.is_primary', true)
            ->assertJsonPath('sections.profile.contacts.1.name', 'Second partner');
    }

    public function test_promoting_the_same_site_twice_does_not_create_duplicate_opportunities(): void
    {
        $user = User::factory()->create();
        $site = Site::query()->create(['name' => 'One site']);

        $this->actingAs($user)->post(route('crm.admin.sites.opportunities.store', $site))->assertRedirect();
        $this->post(route('crm.admin.sites.opportunities.store', $site))->assertRedirect();

        $this->assertSame(1, DB::table('crm_native_opportunities')->count());
    }

    public function test_native_opportunity_can_advance_to_internal_review(): void
    {
        $user = User::factory()->create();
        $site = Site::query()->create(['name' => 'Review site', 'owner_user_id' => $user->id]);
        $this->actingAs($user)->post(route('crm.admin.sites.opportunities.store', $site))->assertRedirect();
        $sourceId = $site->refresh()->legacy_lead_id;
        $version = $this->getJson(route('crm.admin.pipeline.show', $sourceId))
            ->assertOk()->json('opportunity.stage_version');

        $this->patchJson(route('crm.admin.pipeline.stage.update', $sourceId), [
            'stage' => 'INTERNAL_REVIEW', 'expected_version' => $version,
        ])->assertOk()->assertJsonPath('opportunity.stage_code', 'INTERNAL_REVIEW');

        $this->assertDatabaseHas('crm_pipeline_states', ['lead_source_id' => $sourceId, 'stage' => 'INTERNAL_REVIEW']);
    }

    public function test_approved_native_opportunity_appears_in_contracts(): void
    {
        $user = User::factory()->create();
        $site = Site::query()->create(['name' => 'Approved site']);
        $this->actingAs($user)->post(route('crm.admin.sites.opportunities.store', $site))->assertRedirect();
        $sourceId = $site->refresh()->legacy_lead_id;
        $version = $this->getJson(route('crm.admin.pipeline.show', $sourceId))->json('opportunity.stage_version');
        $this->patchJson(route('crm.admin.pipeline.stage.update', $sourceId), [
            'stage' => 'INTERNAL_APPROVED', 'expected_version' => $version,
        ])->assertOk();

        $this->get(route('crm.admin.contracts.index'))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
    }
}
