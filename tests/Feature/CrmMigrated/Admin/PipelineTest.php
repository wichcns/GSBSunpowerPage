<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class PipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_summary_shows_business_conditions_instead_of_probability(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'conditions', ['site_name' => 'Conditions Site', 'commercial_model' => 'RENT', 'rental_cost' => 1200, 'parking_spaces' => 4]);
        $response = $this->actingAs(User::factory()->create())->get('/crm/admin/pipeline')->assertOk();
        $response->assertSee('1,200')->assertSee('4')->assertDontSee('10%', false);
    }

    public function test_pipeline_is_the_main_workspace_for_sites_and_sales(): void
    {
        $user = User::factory()->create();
        Site::create(['site_code' => 'MERGE-1', 'name' => 'Combined Workspace Site']);

        $this->actingAs($user)->get('/crm/admin/pipeline')->assertOk()
            ->assertSee('section=sites', false)
            ->assertDontSee('href="'.route('crm.admin.sites.index').'" class="crm-nav-item', false);
        $this->get('/crm/admin/pipeline?section=sites')->assertOk()
            ->assertSee('Combined Workspace Site')
            ->assertSee('crm-sites', false)
            ->assertSee(__('sites.add'));
    }

    public function test_staff_cannot_open_sites_section_of_pipeline(): void
    {
        $staff = User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);

        $this->actingAs($staff)->get('/crm/admin/pipeline?section=sites')->assertOk()
            ->assertDontSee('class="crm-sites"', false)
            ->assertDontSee('section=sites', false);
    }

    public function test_pipeline_summary_uses_current_rent_and_unit_over_imported_values(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'rent-override', ['site_name' => 'Rent Override Site',
            'commercial_model' => 'RENT', 'rental_cost' => 1200, 'rental_unit' => 'month', 'parking_spaces' => 4]);
        $user = User::factory()->create();
        DB::table('crm_business_terms')->insert([
            'lead_source_id' => 'rent-override',
            'values' => json_encode(['rental_cost' => 2500, 'rental_unit' => 'year', 'parking_spaces' => 6], JSON_THROW_ON_ERROR),
            'version' => 1, 'updated_by_user_id' => $user->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/crm/admin/pipeline')->assertOk();
        $response->assertViewHas('rows', fn ($rows) => $rows->first()['rental_unit'] === 'year');
        $response->assertSee('2,500 / year')->assertDontSee('1,200 / month');
    }

    public function test_guests_cannot_read_pipeline_or_details(): void
    {
        $this->get('/crm/admin/pipeline')->assertRedirect('/crm/login');
        $this->get('/crm/admin/pipeline/lead-1')->assertRedirect('/crm/login');
    }

    public function test_search_filters_and_pagination_read_original_records(): void
    {
        $batch = $this->batch();
        for ($i = 1; $i <= 25; $i++) {
            $this->lead($batch, 'lead-'.$i, [
                'site_name' => $i === 25 ? 'Bangchak special' : 'Site '.$i,
                'area' => $i === 25 ? 'Bangkok' : 'Other',
                'stage' => $i === 25 ? 'MOU' : 'REVIEW',
                'priority' => $i === 25 ? 'high' : 'medium',
            ]);
        }
        $this->actingAs(User::factory()->create());

        $this->get('/crm/admin/pipeline')->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 25 && $rows->count() === 20);
        $this->get('/crm/admin/pipeline?page=2')->assertOk()->assertViewHas('rows', fn ($rows) => $rows->count() === 5);
        $this->get('/crm/admin/pipeline?q=Bangchak&stage=MOU&priority=high')
            ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()['source_id'] === 'lead-25');
        $this->get('/crm/admin/pipeline?stage=UNKNOWN')->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
    }

    public function test_detail_is_exact_and_get_requests_do_not_change_data(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', ['site_name' => 'Named site', 'stage' => 'SIGNED']);
        Site::create(['site_code' => 'S-1', 'name' => 'Named site', 'legacy_lead_id' => 'lead-1']);
        $before = DB::table('crm_legacy_import_batches')->where('id', $batch)->value('updated_at');
        $this->actingAs(User::factory()->create());

        $this->get('/crm/admin/pipeline/lead-1')->assertOk()->assertJsonPath('opportunity.stage_code', 'SIGNED');
        $this->get('/crm/admin/pipeline/missing')->assertNotFound();
        $this->get('/crm/admin/pipeline')->assertOk();
        $this->assertDatabaseCount('crm_legacy_records', 1);
        $this->assertDatabaseCount('crm_sites', 1);
        $this->assertSame($before, DB::table('crm_legacy_import_batches')->where('id', $batch)->value('updated_at'));
    }

    public function test_directory_renders_stage_groups_safe_text_and_read_only_drawer(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', [
            'site_name' => '<script>alert(1)</script>', 'stage' => 'MOU_SIGNING', 'priority' => 'high',
            'probability' => 65, 'next_action' => 'Call partner',
        ]);
        Site::create(['site_code' => 'S-1', 'name' => '<script>alert(1)</script>', 'legacy_lead_id' => 'lead-1']);
        $this->lead($batch, 'lead-2', ['site_name' => 'Unmatched', 'stage' => 'ON_HOLD']);

        $this->actingAs(User::factory()->create())->get('/crm/admin/pipeline')
            ->assertOk()->assertSee('ผ่านการตรวจสอบและ MOU')->assertSee('พักและยกเลิก')
            ->assertSee('ขั้นตอนลงนาม MOU')->assertSee('Call partner')
            ->assertSee('ข้อมูลสถานที่ไม่ตรงกับรายการนำเข้า')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('title="ดูรายละเอียด"', false)
            ->assertSee('class="crm-pipeline', false)
            ->assertSee('xl:block', false)
            ->assertSee('section=sites&amp;site=', false);
    }

    public function test_pipeline_renders_chinese_without_thai_system_copy(): void
    {
        $response = $this->actingAs(User::factory()->create(['locale' => 'zh']))
            ->get('/crm/admin/pipeline')->assertOk()
            ->assertSee('销售流程')->assertSee('销售商机')->assertSee('全部商机');

        $systemContent = preg_replace('/<option value="th"[^>]*>ไทย<\/option>/', '', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/[ก-๙]/u', $systemContent);
    }

    public function test_unknown_imported_stage_and_priority_remain_selectable(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'odd', ['stage' => 'CUSTOM', 'priority' => 'urgent']);
        $this->actingAs(User::factory()->create())->get('/crm/admin/pipeline')
            ->assertOk()->assertSee('value="CUSTOM"', false)->assertSee('value="urgent"', false);
        $this->get('/crm/admin/pipeline?stage=CUSTOM&priority=urgent')
            ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
    }

    public function test_board_view_groups_every_filtered_record_and_preserves_view_when_filtering(): void
    {
        $batch = $this->batch();
        for ($i = 1; $i <= 25; $i++) {
            $this->lead($batch, 'lead-'.$i, [
                'site_name' => 'Board Site '.$i,
                'stage' => $i === 25 ? 'CONTRACT_SIGNED' : 'MOU_SIGNING',
                'priority' => $i === 25 ? 'high' : 'medium',
            ]);
        }
        $this->actingAs(User::factory()->create());

        $this->get('/crm/admin/pipeline?view=board')->assertOk()
            ->assertViewHas('viewMode', 'board')
            ->assertViewHas('boardGroups', fn ($groups) => count($groups['review_mou']) === 24 && count($groups['contract_signed']) === 1)
            ->assertSee('Board Site 25')
            ->assertSee('Board Site 1');

        $this->get('/crm/admin/pipeline?view=board&stage=CONTRACT_SIGNED&priority=high')->assertOk()
            ->assertViewHas('boardGroups', fn ($groups) => count($groups['contract_signed']) === 1 && count($groups['review_mou']) === 0)
            ->assertSee('name="view" value="board"', false);

        $this->get('/crm/admin/pipeline?view=unexpected')->assertOk()->assertViewHas('viewMode', 'table');
    }

    public function test_status_cards_show_counts_without_repeating_total_under_each_card(): void
    {
        $batch = $this->batch();
        $this->lead($batch, 'lead-1', ['stage' => 'NEW']);
        $this->lead($batch, 'lead-2', ['stage' => 'REVIEW']);
        $this->lead($batch, 'lead-3', ['stage' => 'MOU']);
        $this->lead($batch, 'lead-4', ['stage' => 'ON_HOLD']);

        $this->actingAs(User::factory()->create())->get('/crm/admin/pipeline')
            ->assertOk()
            ->assertSee('โอกาสทั้งหมด 4 รายการ')
            ->assertDontSee('จาก 4 โอกาสทั้งหมด');
    }

    private function batch(): int
    {
        return DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('b', 64), 'source_name' => 'fixture.json', 'exported_at' => '2026-10-16T00:00:00Z',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function lead(int $batch, string $id, array $fields): void
    {
        $payload = json_encode(array_merge([
            'id' => $id, 'site_name' => 'Fixture', 'area' => 'Other', 'stage' => 'NEW',
            'priority' => 'medium', 'probability' => 10,
        ], $fields));
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $id,
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
