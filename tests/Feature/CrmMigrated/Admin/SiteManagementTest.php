<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class SiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_site_records(): void
    {
        $this->get('/crm/admin/sites')->assertRedirect('/crm/login');
        $this->post('/crm/admin/sites', ['name' => 'Test'])->assertRedirect('/crm/login');
    }

    public function test_new_site_returns_to_the_pipeline_sites_section(): void
    {
        $this->actingAs(User::factory()->create())->post(route('crm.admin.sites.store'), ['name' => 'New Workspace Site'])
            ->assertRedirect(route('crm.admin.pipeline.index', ['section' => 'sites', 'site' => 1]));
    }

    public function test_staff_cannot_submit_contact_removals(): void
    {
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
        $site = Site::create(['name' => 'Protected site']);
        $contact = $site->contacts()->create(['name' => 'Protected contact']);

        $this->actingAs($staff)->patch(route('crm.admin.sites.update', $site), [
            'name' => 'Protected site',
            'site_fingerprint' => str_repeat('a', 64),
            'deleted_contact_ids' => [$contact->id],
        ])->assertForbidden();

        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id]);
    }

    public function test_site_directory_has_its_own_full_width_layout_hook(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('crm.admin.sites.index'))
            ->assertOk()
            ->assertSee('class="crm-sites"', false);
    }

    public function test_site_editor_submits_queued_saved_contact_removals_without_affecting_legacy_preview(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('crm.admin.sites.index'))
            ->assertOk()
            ->assertSee('name="deleted_contact_ids[]"', false)
            ->assertSee('removeContact(index)', false)
            ->assertSee(__('sites.not_added'));
    }

    public function test_admin_can_create_site_with_multiple_contacts_and_audit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('crm.admin.sites.store'), [
            'name' => 'Bangchak Bang Bua Thong Bank',
            'area' => 'Bang Bua Thong',
            'location_type' => 'PETROL_STATION',
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

    public function test_site_directory_searches_contact_line_name_and_email_and_shows_primary_contact_detail(): void
    {
        $user = User::factory()->create();
        $lineSite = Site::create(['name' => 'LINE site']);
        $lineSite->contacts()->create(['name' => 'Line contact', 'line_name' => 'line-only', 'is_primary' => true]);
        $emailSite = Site::create(['name' => 'Email site']);
        $emailSite->contacts()->create(['name' => 'Email contact', 'email' => 'contact@example.test', 'is_primary' => true]);
        Site::create(['name' => 'Other site']);

        $this->actingAs($user)->get(route('crm.admin.sites.index', ['q' => 'line-only']))
            ->assertOk()->assertSee('LINE site')->assertDontSee('Email site')->assertSee('LINE: line-only');
        $this->get(route('crm.admin.sites.index', ['q' => 'contact@example.test']))
            ->assertOk()->assertSee('Email site')->assertDontSee('LINE site')->assertSee('contact@example.test');
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

    public function test_update_removes_a_primary_contact_and_promotes_the_remaining_contact(): void
    {
        $user = User::factory()->create();
        $site = Site::create(['name' => 'Contact site']);
        $primary = $site->contacts()->create(['name' => 'Primary', 'is_primary' => true]);
        $remaining = $site->contacts()->create(['name' => 'Remaining', 'is_primary' => false]);
        $fingerprint = $this->actingAs($user)->getJson(route('crm.admin.sites.show', $site))->json('site.edit_fingerprint');

        $this->patch(route('crm.admin.sites.update', $site), [
            'name' => 'Contact site',
            'site_fingerprint' => $fingerprint,
            'contacts' => [['id' => $remaining->id, 'name' => 'Remaining', 'is_primary' => '0']],
            'deleted_contact_ids' => [$primary->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('crm_contacts', ['id' => $primary->id]);
        $this->assertDatabaseHas('crm_contacts', ['id' => $remaining->id, 'is_primary' => 1]);
        $this->assertDatabaseHas('crm_site_audits', ['site_id' => $site->id, 'action' => 'updated']);
    }

    public function test_update_can_remove_the_final_contact_without_deleting_its_site(): void
    {
        $user = User::factory()->create();
        $site = Site::create(['name' => 'Contact site']);
        $contact = $site->contacts()->create(['name' => 'Only contact', 'is_primary' => true]);
        $fingerprint = $this->actingAs($user)->getJson(route('crm.admin.sites.show', $site))->json('site.edit_fingerprint');

        $this->patch(route('crm.admin.sites.update', $site), [
            'name' => 'Contact site',
            'site_fingerprint' => $fingerprint,
            'contacts' => [],
            'deleted_contact_ids' => [$contact->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_sites', ['id' => $site->id]);
        $this->assertDatabaseMissing('crm_contacts', ['id' => $contact->id]);
    }

    public function test_update_rejects_deleting_a_contact_belonging_to_another_site(): void
    {
        $user = User::factory()->create();
        $first = Site::create(['name' => 'First site']);
        $second = Site::create(['name' => 'Second site']);
        $contact = $second->contacts()->create(['name' => 'Second contact']);
        $fingerprint = $this->actingAs($user)->getJson(route('crm.admin.sites.show', $first))->json('site.edit_fingerprint');

        $this->patch(route('crm.admin.sites.update', $first), [
            'name' => 'Changed first site',
            'site_fingerprint' => $fingerprint,
            'deleted_contact_ids' => [$contact->id],
        ])->assertSessionHasErrors('deleted_contact_ids.0');

        $this->assertDatabaseHas('crm_sites', ['id' => $first->id, 'name' => 'First site']);
        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id]);
    }

    public function test_stale_site_form_cannot_overwrite_a_newer_site_and_contact_edit(): void
    {
        $user = User::factory()->create();
        $site = Site::create(['name' => 'Before', 'area' => 'Bangkok']);
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
            'deleted_contact_ids' => [$contact->id],
        ])->assertSessionHasErrors('site_fingerprint');

        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'name' => 'Newer edit']);
        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id, 'name' => 'Newer contact']);
        $this->assertDatabaseCount('crm_site_audits', 1);
    }

    public function test_directory_shows_the_legacy_owner_label_without_creating_a_user(): void
    {
        $user = User::factory()->create();
        Site::create(['name' => 'Legacy site', 'legacy_owner_label' => 'Kira']);

        $this->actingAs($user)->get(route('crm.admin.sites.index'))
            ->assertOk()->assertSee('Legacy site')->assertSee('Kira');

        $this->assertDatabaseCount('crm_users', 1);
    }

    public function test_linked_site_detail_exposes_its_exact_pipeline_record_url(): void
    {
        $user = User::factory()->create();
        $linked = Site::create(['name' => 'Linked site', 'legacy_lead_id' => 'lead-1']);
        $unlinked = Site::create(['name' => 'New site']);

        $this->actingAs($user)->getJson(route('crm.admin.sites.show', $linked))
            ->assertOk()
            ->assertJsonPath('site.pipeline_url', route('crm.admin.pipeline.index', ['open' => 'lead-1']));
        $this->getJson(route('crm.admin.sites.show', $unlinked))
            ->assertOk()
            ->assertJsonPath('site.pipeline_url', null);
    }
}
