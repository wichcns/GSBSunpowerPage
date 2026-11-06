<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\ReadModels\ManagementReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class ManagementReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_report_uses_current_state_and_renders_accessible_charts_and_attention(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        $admin = $this->account('admin', 'Admin Report');
        $staff = $this->account('staff', 'Alice Staff');
        $this->leadBatch([
            'signed' => ['site_name' => 'Signed Site', 'area' => 'Bangkok', 'stage' => 'CONTRACT', 'contract_status' => 'draft', 'contract_term' => '3+3+3', 'contract_expiry' => '2026-11-20'],
            'late' => ['site_name' => 'Late Site', 'area' => 'Bangkok', 'stage' => 'NEGOTIATING', 'next_action' => 'โทรติดตาม', 'next_action_at' => '2026-10-20 09:00:00'],
        ]);
        Site::create(['site_code' => 'SITE-1', 'name' => 'Signed Site', 'area' => 'Bangkok', 'location_type' => 'Gas station', 'legacy_lead_id' => 'signed', 'owner_user_id' => $staff->id]);
        Site::create(['site_code' => 'SITE-2', 'name' => 'Late Site', 'area' => 'Bangkok', 'location_type' => 'Community mall', 'legacy_lead_id' => 'late', 'owner_user_id' => $staff->id]);
        DB::table('crm_pipeline_states')->insert(['lead_source_id' => 'signed', 'stage' => 'SIGNED', 'probability' => 100, 'updated_by_user_id' => $admin->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('crm_engineering_projects')->insert(['lead_source_id' => 'signed', 'project_code' => 'ENG-1', 'engineering_stage' => 'CONSTRUCTION', 'origin' => 'current', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->get(route('crm.admin.reports.index'))->assertOk()
            ->assertSee('รายงานผู้บริหาร')
            ->assertSee('สถานที่ทั้งหมด')->assertSee('ลงนามแล้ว')->assertSee('กำลังก่อสร้าง')
            ->assertSee('ขั้นตอนการขาย')->assertSee('สัดส่วนขั้นวิศวกรรม')
            ->assertSee('ภาระงานตามผู้รับผิดชอบ')->assertSee('สถานะสัญญา')
            ->assertSee('Late Site')->assertSee('Signed Site')
            ->assertSee('aria-label="กราฟขั้นตอนการขาย"', false)
            ->assertSee('href="'.route('crm.admin.reports.export').'"', false);
        $report = app(ManagementReport::class)->forUser($admin);
        $this->assertSame(['Bangkok' => 2], $report['locations']);
        $this->actingAs($admin)->get(route('crm.admin.reports.index'))->assertOk()
            ->assertSee('จำนวนสถานที่ตามพื้นที่')->assertSee('Signed Site')->assertSee('Late Site')
            ->assertSee('โทรติดตาม');
    }

    public function test_filters_change_cards_charts_attention_and_csv_from_the_same_rows(): void
    {
        $admin = $this->account('admin', 'Admin Filter');
        $first = $this->account('staff', 'First Owner');
        $second = $this->account('staff', 'Second Owner');
        $this->leadBatch([
            'first' => ['site_name' => 'First Site', 'stage' => 'NEW', 'next_action_at' => '2026-10-25 08:00:00'],
            'second' => ['site_name' => 'Second Site', 'stage' => 'SIGNED', 'next_action_at' => '2026-11-05 08:00:00'],
        ]);
        Site::create(['name' => 'First Site', 'area' => 'North', 'location_type' => 'Mall', 'legacy_lead_id' => 'first', 'owner_user_id' => $first->id]);
        Site::create(['name' => 'Second Site', 'area' => 'South', 'location_type' => 'Station', 'legacy_lead_id' => 'second', 'owner_user_id' => $second->id]);

        $query = ['owner' => $first->id, 'area' => 'North', 'sales_stage' => 'NEW', 'date_from' => '2026-10-01', 'date_to' => '2026-10-31'];
        $this->actingAs($admin)->get(route('crm.admin.reports.index', $query))->assertOk()
            ->assertSee('First Site')->assertDontSee('Second Site')
            ->assertViewHas('report', fn (array $report) => $report['kpis']['sites'] === 1
                && $report['locations'] === ['North' => 1]
                && $report['sales']['NEW']['count'] === 1
                && array_sum(array_column($report['sales'], 'count')) === 1);

        $xlsx = $this->get(route('crm.admin.reports.export', $query))->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->streamedContent();
        $parts = $this->xlsxParts($xlsx);
        $this->assertStringContainsString('First Site', $parts['xl/sharedStrings.xml']);
        $this->assertStringNotContainsString('Second Site', $parts['xl/sharedStrings.xml']);
        $this->assertStringContainsString('<table ', $parts['xl/tables/table1.xml']);
        $this->assertStringContainsString('autoFilter', $parts['xl/tables/table1.xml']);
        $this->assertStringContainsString('state="frozen"', $parts['xl/worksheets/sheet1.xml']);
    }

    public function test_manager_sees_only_staff_owned_rows_and_staff_cannot_access_reports(): void
    {
        $manager = $this->account('manager', 'Manager Report');
        $staff = $this->account('staff', 'Staff Owner');
        $admin = $this->account('admin', 'Admin Owner');
        $this->leadBatch([
            'staff' => ['site_name' => 'Staff Site', 'stage' => 'NEW'],
            'admin' => ['site_name' => 'Admin Site', 'stage' => 'SIGNED'],
        ]);
        Site::create(['name' => 'Staff Site', 'legacy_lead_id' => 'staff', 'owner_user_id' => $staff->id]);
        Site::create(['name' => 'Admin Site', 'legacy_lead_id' => 'admin', 'owner_user_id' => $admin->id]);

        $this->actingAs($manager)->get(route('crm.admin.reports.index'))->assertOk()
            ->assertSee('Staff Site')->assertDontSee('Admin Site')
            ->assertViewHas('report', fn (array $report) => $report['scope'] === 'staff_all' && count($report['rows']) === 1);
        $xlsx = $this->get(route('crm.admin.reports.export'))->assertOk()->streamedContent();
        $strings = $this->xlsxParts($xlsx)['xl/sharedStrings.xml'];
        $this->assertStringContainsString('Staff Site', $strings);
        $this->assertStringNotContainsString('Admin Site', $strings);

        $this->actingAs($staff)->get('/crm/admin/reports')->assertForbidden();
        $this->get('/crm/admin/reports/export')->assertForbidden();
        $this->get('/crm/admin')->assertOk()->assertDontSee('href="'.route('crm.admin.reports.index').'"', false);
    }

    public function test_report_uses_the_same_mou_group_for_old_and_current_records(): void
    {
        $admin = $this->account('admin', 'Stage Report');
        $this->leadBatch([
            'old' => ['site_name' => 'Old MOU', 'stage' => 'MOU'],
            'current' => ['site_name' => 'Current MOU', 'stage' => 'MOU_SIGNED'],
            'other' => ['site_name' => 'Contract Site', 'stage' => 'CONTRACT_SIGNING'],
        ]);

        $report = app(ManagementReport::class)->forUser($admin, ['sales_stage' => 'MOU']);
        $this->assertCount(2, $report['rows']);
        $this->assertSame(2, $report['sales']['MOU']['count']);
        $this->assertSame(2, $report['kpis']['sites']);
    }

    public function test_excel_export_keeps_formula_like_customer_text_as_text(): void
    {
        $admin = $this->account('admin', 'Admin CSV');
        $this->leadBatch(['danger' => ['site_name' => '=2+2', 'stage' => 'NEW']]);
        Site::create(['name' => '=2+2', 'legacy_lead_id' => 'danger']);

        $xlsx = $this->actingAs($admin)->get(route('crm.admin.reports.export'))->assertOk()->streamedContent();
        $parts = $this->xlsxParts($xlsx);
        $this->assertStringContainsString('=2+2', $parts['xl/sharedStrings.xml']);
        $this->assertStringNotContainsString('<f>', $parts['xl/worksheets/sheet1.xml']);
        $this->assertStringContainsString('รายงาน CRM', $parts['xl/workbook.xml']);
    }

    private function account(string $role, string $name): User
    {
        return User::factory()->create(['name' => $name,
            'role_id' => DB::table('crm_roles')->where('slug', $role)->value('id')]);
    }

    private function leadBatch(array $leads): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'report-test', 'exported_at' => '2026-10-26',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($leads as $sourceId => $payload) {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
            DB::table('crm_legacy_records')->insert([
                'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
                'payload' => $json, 'payload_sha256' => hash('sha256', $json),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function xlsxParts(string $content): array
    {
        $path = tempnam(sys_get_temp_dir(), 'crm-xlsx-');
        file_put_contents($path, $content);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true, 'ดาวน์โหลดต้องเป็นไฟล์ XLSX ที่เปิดได้');
        $parts = [];
        foreach (['xl/sharedStrings.xml', 'xl/tables/table1.xml', 'xl/worksheets/sheet1.xml', 'xl/workbook.xml'] as $name) {
            $value = $zip->getFromName($name);
            $this->assertNotFalse($value, "XLSX ต้องมี {$name}");
            $parts[$name] = $value;
        }
        $zip->close();
        unlink($path);

        return $parts;
    }
}
