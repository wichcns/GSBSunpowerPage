<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Contact;
use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Services\GlobalCrmSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_search_sites_contacts_pipeline_and_contracts_from_one_query(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'global-lead', [
            'site_name' => 'North Hub',
            'site_code' => 'NH-100',
            'area' => 'Bangkok',
            'stage' => 'CONTRACT_SIGNED',
            'contract_status' => 'Signed',
        ]);
        $site = Site::create(['site_code' => 'NH-100', 'name' => 'North Hub', 'area' => 'Bangkok', 'legacy_lead_id' => 'global-lead']);
        Contact::create(['site_id' => $site->id, 'name' => 'Narin Partner', 'phone' => '0812345678', 'line_name' => 'narin.line', 'email' => 'narin@example.test', 'is_primary' => true]);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/crm/admin/search?q=North')
            ->assertOk()
            ->assertSee('North Hub')
            ->assertSee('Narin Partner')
            ->assertSee('/crm/admin/pipeline?open=global-lead', false)
            ->assertSee('/crm/admin/contracts?q=North', false);
    }

    public function test_blank_global_search_returns_to_dashboard(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/crm/admin/search')
            ->assertRedirect('/crm/admin');
    }

    public function test_staff_search_only_returns_assigned_pipeline_records(): void
    {
        $staff = $this->userWithRole('staff');
        $batch = $this->batch();
        $this->lead($batch, 'assigned-lead', ['site_name' => 'Search Fixture Assigned']);
        $this->lead($batch, 'private-lead', ['site_name' => 'Search Fixture Private']);
        Site::create(['site_code' => 'ASSIGNED-1', 'name' => 'Search Fixture Assigned',
            'legacy_lead_id' => 'assigned-lead', 'owner_user_id' => $staff->id]);

        $results = app(GlobalCrmSearch::class)->for($staff, 'Search Fixture');

        $this->assertArrayNotHasKey('sites', $results);
        $this->assertArrayNotHasKey('contracts', $results);
        $this->assertSame(['assigned-lead'], array_column($results['pipeline'], 'source_id'));
    }

    public function test_new_site_opportunity_is_found_in_both_site_and_pipeline_results(): void
    {
        $admin = $this->userWithRole('admin');
        $site = Site::create(['site_code' => 'DEMO-SEARCH', 'name' => 'Search Fixture New Site',
            'legacy_lead_id' => 'native-site-demo']);
        DB::table('crm_native_opportunities')->insert([
            'site_id' => $site->id, 'source_id' => 'native-site-demo',
            'payload' => json_encode(['id' => 'native-site-demo', 'site_name' => $site->name,
                'stage' => 'NEW', 'priority' => 'medium', 'probability' => 10], JSON_THROW_ON_ERROR),
            'created_by_user_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $results = app(GlobalCrmSearch::class)->for($admin, 'Search Fixture New');

        $this->assertSame([$site->id], $results['sites']->pluck('id')->all());
        $this->assertSame(['native-site-demo'], array_column($results['pipeline'], 'source_id'));
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', $slug)->value('id')]);
    }

    private function batch(): int
    {
        return DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('c', 64), 'source_name' => 'fixture.json', 'exported_at' => '2026-10-29T00:00:00Z',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function lead(int $batch, string $id, array $fields): void
    {
        $payload = json_encode(array_merge([
            'id' => $id, 'site_name' => 'Fixture', 'area' => 'Other', 'stage' => 'NEW', 'priority' => 'medium', 'probability' => 10,
        ], $fields), JSON_THROW_ON_ERROR);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $id, 'payload' => $payload,
            'payload_sha256' => hash('sha256', $payload), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
