<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class SiteOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
    }

    public function test_guest_cannot_review_or_assign_site_owners(): void
    {
        $this->get('/crm/admin/ownership')->assertRedirect('/crm/login');
        $this->post('/crm/admin/ownership/assign', [])->assertRedirect('/crm/login');
    }

    public function test_review_lists_unassigned_sites_and_name_hints_without_assigning_anyone(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Kira']));
        $site = Site::create(['name' => 'Legacy site', 'legacy_owner_label' => 'Kira', 'legacy_lead_id' => 'lead-1']);
        Site::create(['name' => 'Unknown site', 'legacy_owner_label' => 'Marketing']);
        Site::create(['name' => 'Assigned site', 'owner_user_id' => auth()->id()]);

        $this->get('/crm/admin/ownership')->assertOk()
            ->assertSee('Legacy site')->assertSee('Unknown site')->assertDontSee('Assigned site')
            ->assertSee('Kira')->assertSee('Marketing');
        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'owner_user_id' => null]);
        $this->get('/crm/admin/ownership?label=Kira')->assertOk()
            ->assertSee('Legacy site')->assertDontSee('Unknown site');
    }

    public function test_explicit_batch_confirmation_changes_only_owner_and_writes_audit(): void
    {
        $actor = User::factory()->create();
        $owner = $this->staff();
        $owner->update(['name' => 'Kira']);
        $first = Site::create(['name' => 'First', 'legacy_owner_label' => 'Kira', 'notes' => 'Preserve me', 'legacy_lead_id' => 'lead-1']);
        $second = Site::create(['name' => 'Second', 'legacy_owner_label' => 'Marketing', 'legacy_lead_id' => 'lead-2']);

        $this->actingAs($actor)->post('/crm/admin/ownership/assign', [
            'owner_user_id' => $owner->id,
            'site_ids' => [$first->id, $second->id],
            'fingerprints' => [$first->id => SiteEditFingerprint::for($first), $second->id => SiteEditFingerprint::for($second)],
        ])->assertRedirect('/crm/admin/ownership');

        $this->assertDatabaseHas('crm_sites', ['id' => $first->id, 'owner_user_id' => $owner->id,
            'legacy_owner_label' => 'Kira', 'notes' => 'Preserve me']);
        $this->assertDatabaseHas('crm_sites', ['id' => $second->id, 'owner_user_id' => $owner->id,
            'legacy_owner_label' => 'Marketing']);
        $this->assertDatabaseCount('crm_site_audits', 2);
        $this->assertDatabaseHas('crm_site_audits', ['site_id' => $first->id, 'actor_user_id' => $actor->id,
            'action' => 'owner_assigned']);
        $this->get('/crm/admin/ownership')->assertOk()->assertDontSee('First')->assertDontSee('Second');
    }

    public function test_stale_batch_does_not_partially_assign_any_site(): void
    {
        $actor = User::factory()->create();
        $owner = $this->staff();
        $first = Site::create(['name' => 'First']);
        $second = Site::create(['name' => 'Second']);
        $fingerprints = [$first->id => SiteEditFingerprint::for($first), $second->id => SiteEditFingerprint::for($second)];
        $second->update(['area' => 'Changed elsewhere']);

        $this->actingAs($actor)->post('/crm/admin/ownership/assign', [
            'owner_user_id' => $owner->id, 'site_ids' => [$first->id, $second->id], 'fingerprints' => $fingerprints,
        ])->assertSessionHasErrors('site_ids');

        $this->assertDatabaseHas('crm_sites', ['id' => $first->id, 'owner_user_id' => null]);
        $this->assertDatabaseHas('crm_sites', ['id' => $second->id, 'owner_user_id' => null, 'area' => 'Changed elsewhere']);
        $this->assertDatabaseCount('crm_site_audits', 0);
    }

    public function test_invalid_owner_or_site_selection_is_rejected_without_writes(): void
    {
        $actor = User::factory()->create();
        $site = Site::create(['name' => 'Safe site']);
        $this->actingAs($actor)->post('/crm/admin/ownership/assign', [
            'owner_user_id' => 999999, 'site_ids' => [$site->id],
            'fingerprints' => [$site->id => SiteEditFingerprint::for($site)],
        ])->assertSessionHasErrors('owner_user_id');
        $this->post('/crm/admin/ownership/assign', [
            'owner_user_id' => $actor->id, 'site_ids' => [$site->id, $site->id],
            'fingerprints' => [$site->id => SiteEditFingerprint::for($site)],
        ])->assertSessionHasErrors('site_ids.1');
        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'owner_user_id' => null]);
        $this->assertDatabaseCount('crm_site_audits', 0);
    }

    public function test_confirmed_owner_gets_legacy_follow_up_in_my_work(): void
    {
        $actor = User::factory()->create();
        $owner = $this->staff();
        $site = Site::create(['name' => 'Follow-up site', 'legacy_lead_id' => 'lead-1', 'legacy_owner_label' => 'Old free text']);
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('f', 64), 'source_name' => 'ownership-test', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => json_encode(['site_name' => 'Follow-up site', 'stage' => 'NEGOTIATING', 'next_action' => 'Call customer']),
            'payload_sha256' => hash('sha256', 'lead-1'), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($owner)->get('/crm/admin/my-work')->assertOk()->assertDontSee('Call customer');
        $this->actingAs($actor)->post('/crm/admin/ownership/assign', [
            'owner_user_id' => $owner->id, 'site_ids' => [$site->id],
            'fingerprints' => [$site->id => SiteEditFingerprint::for($site)],
        ])->assertRedirect('/crm/admin/ownership');
        $this->actingAs($owner)->get('/crm/admin/my-work')->assertOk()->assertSee('Call customer');
        $this->actingAs($actor)->get('/crm/admin/my-work')->assertOk()->assertDontSee('Call customer');
    }

    public function test_assignment_choices_include_only_staff_accounts(): void
    {
        $actor = User::factory()->create(['name' => 'Super One']);
        User::factory()->create(['name' => 'Manager One', 'role_id' => DB::table('crm_roles')->where('slug', 'manager')->value('id')]);
        $staff = $this->staff();
        $staff->update(['name' => 'Staff One']);
        Site::create(['name' => 'Review site', 'legacy_owner_label' => 'Manager One']);

        $this->actingAs($actor)->get('/crm/admin/ownership')->assertOk()
            ->assertSee('Staff One')->assertDontSee('Super One ·')->assertDontSee('Manager One ·')
            ->assertSee('บัญชี Staff 1 บัญชี');
    }

    public function test_non_staff_account_cannot_be_assigned_even_by_direct_post(): void
    {
        $actor = User::factory()->create();
        $site = Site::create(['name' => 'Protected site']);

        foreach (['super_admin', 'admin', 'manager'] as $slug) {
            $recipient = User::factory()->create([
                'role_id' => DB::table('crm_roles')->where('slug', $slug)->value('id'),
            ]);
            $this->actingAs($actor)->post('/crm/admin/ownership/assign', [
                'owner_user_id' => $recipient->id,
                'site_ids' => [$site->id],
                'fingerprints' => [$site->id => SiteEditFingerprint::for($site)],
            ])->assertSessionHasErrors('owner_user_id');
        }

        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'owner_user_id' => null]);
        $this->assertDatabaseCount('crm_site_audits', 0);
    }
}
