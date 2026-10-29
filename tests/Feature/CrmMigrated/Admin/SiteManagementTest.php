<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class SiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_site_records(): void
    {
        $this->get('/crm/admin/sites')->assertRedirect('/crm/login');
        $this->post('/crm/admin/sites', ['name' => 'Test'])->assertRedirect('/crm/login');
    }

    public function test_site_directory_has_its_own_full_width_layout_hook(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('crm.admin.sites.index'))
            ->assertOk()
            ->assertSee('class="crm-sites"', false);
    }

    public function test_admin_can_create_site_with_multiple_contacts_and_audit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('crm.admin.sites.store'), [
            'name' => 'Bangchak Bang Bua Thong Bank',
            'area' => 'Bang Bua Thong',
            'location_type' => 'Petrol station',
            'contacts' => [
                ['name' => 'Khun Bank', 'phone' => '0812345678', 'is_primary' => '1'],
                ['name' => 'Khun Lek', 'line_name' => 'lek', 'is_primary' => '0'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_sites', ['site_code' => 'SITE-000001', 'name' => 'Bangchak Bang Bua Thong Bank']);
        $this->assertDatabaseCount('crm_contacts', 2);
        $this->assertDatabaseHas('crm_contacts', ['name' => 'Khun Bank', 'is_primary' => 1]);
        $this->assertDatabaseHas('crm_site_audits', ['actor_user_id' => $user->id, 'action' => 'created']);
    }

    public function test_invalid_contact_prevents_partial_site_creation(): void
    {
        $this->actingAs(User::factory()->create())->post(route('crm.admin.sites.store'), [
            'name' => 'Test site',
            'contacts' => [['name' => '', 'is_primary' => '1']],
        ])->assertSessionHasErrors('contacts.0.name');

        $this->assertDatabaseCount('crm_sites', 0);
    }

    public function test_site_directory_can_search_and_show_details(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('crm.admin.sites.store'), ['name' => 'Bangchak Bank', 'area' => 'Nonthaburi']);
        $this->actingAs($user)->post(route('crm.admin.sites.store'), ['name' => 'Other place', 'area' => 'Bangkok']);

        $this->actingAs($user)->get(route('crm.admin.sites.index', ['q' => 'Bangchak']))
            ->assertOk()->assertSee('Bangchak Bank')->assertDontSee('Other place');

        $this->actingAs($user)->get(route('crm.admin.sites.show', 1))
            ->assertOk()->assertJsonPath('site.name', 'Bangchak Bank');
    }

    public function test_update_rejects_a_contact_belonging_to_another_site(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('crm.admin.sites.store'), [
            'name' => 'First site', 'contacts' => [['name' => 'First contact']],
        ]);
        $this->actingAs($user)->post(route('crm.admin.sites.store'), [
            'name' => 'Second site', 'contacts' => [['name' => 'Second contact']],
        ]);

        $this->actingAs($user)->patch(route('crm.admin.sites.update', 1), [
            'name' => 'Changed first site',
            'contacts' => [['id' => 2, 'name' => 'Stolen contact']],
        ])->assertSessionHasErrors('contacts.0.id');

        $this->assertDatabaseHas('crm_sites', ['id' => 1, 'name' => 'First site']);
        $this->assertDatabaseHas('crm_contacts', ['id' => 2, 'name' => 'Second contact']);
    }

    public function test_update_changes_the_primary_contact_and_records_audit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('crm.admin.sites.store'), [
            'name' => 'Test site',
            'contacts' => [['name' => 'First', 'is_primary' => '1'], ['name' => 'Second', 'is_primary' => '0']],
        ]);

        $fingerprint = $this->getJson(route('crm.admin.sites.show', 1))->json('site.edit_fingerprint');
        $this->actingAs($user)->patch(route('crm.admin.sites.update', 1), [
            'name' => 'Renamed site',
            'site_fingerprint' => $fingerprint,
            'contacts' => [['id' => 1, 'name' => 'First', 'is_primary' => '0'], ['id' => 2, 'name' => 'Second', 'is_primary' => '1']],
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_sites', ['id' => 1, 'name' => 'Renamed site']);
        $this->assertDatabaseHas('crm_contacts', ['id' => 1, 'is_primary' => 0]);
        $this->assertDatabaseHas('crm_contacts', ['id' => 2, 'is_primary' => 1]);
        $this->assertDatabaseHas('crm_site_audits', ['site_id' => 1, 'action' => 'updated']);
    }

    public function test_stale_site_form_cannot_overwrite_a_newer_site_and_contact_edit(): void
    {
        $user = User::factory()->create();
        $site = \App\Crm\Models\Site::create(['name' => 'Before', 'area' => 'Bangkok']);
        $contact = $site->contacts()->create(['name' => 'First contact', 'is_primary' => true]);
        $this->actingAs($user);

        $staleFingerprint = $this->getJson(route('crm.admin.sites.show', $site))
            ->assertOk()->json('site.edit_fingerprint');
        $this->assertIsString($staleFingerprint);
        $this->assertSame(64, strlen($staleFingerprint));

        $this->patch(route('crm.admin.sites.update', $site), [
            'name' => 'Newer edit', 'area' => 'Bangkok', 'site_fingerprint' => $staleFingerprint,
            'contacts' => [['id' => $contact->id, 'name' => 'Newer contact', 'is_primary' => '1']],
        ])->assertRedirect();

        $this->patch(route('crm.admin.sites.update', $site), [
            'name' => 'Stale edit', 'area' => 'Bangkok', 'site_fingerprint' => $staleFingerprint,
            'contacts' => [['id' => $contact->id, 'name' => 'Stale contact', 'is_primary' => '1']],
        ])->assertSessionHasErrors('site_fingerprint');

        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'name' => 'Newer edit']);
        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id, 'name' => 'Newer contact']);
        $this->assertDatabaseCount('crm_site_audits', 1);
    }

    public function test_directory_shows_the_legacy_owner_label_without_creating_a_user(): void
    {
        $user = User::factory()->create();
        \App\Crm\Models\Site::create(['name' => 'Legacy site', 'legacy_owner_label' => 'Kira']);

        $this->actingAs($user)->get(route('crm.admin.sites.index'))
            ->assertOk()->assertSee('Legacy site')->assertSee('Kira');

        $this->assertDatabaseCount('crm_users', 1);
    }

    public function test_linked_site_detail_exposes_its_exact_pipeline_record_url(): void
    {
        $user = User::factory()->create();
        $linked = \App\Crm\Models\Site::create(['name' => 'Linked site', 'legacy_lead_id' => 'lead-1']);
        $unlinked = \App\Crm\Models\Site::create(['name' => 'New site']);

        $this->actingAs($user)->getJson(route('crm.admin.sites.show', $linked))
            ->assertOk()
            ->assertJsonPath('site.pipeline_url', route('crm.admin.pipeline.index', ['open' => 'lead-1']));
        $this->getJson(route('crm.admin.sites.show', $unlinked))
            ->assertOk()
            ->assertJsonPath('site.pipeline_url', null);
    }
}
