<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class ActivityCenterTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role): User
    {
        return User::factory()->create([
            'role_id' => DB::table('crm_roles')->where('slug', $role)->value('id'),
        ]);
    }

    private function seedLead(User $owner): void
    {
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'activity-page', 'exported_at' => '2026-10-26',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $payload = json_encode([
            'id' => 'lead-1', 'site_name' => 'Activity Site', 'stage' => 'NEGOTIATING',
            'next_action' => 'Call decision maker', 'next_action_at' => '2026-10-27T09:00:00',
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'lead-1',
            'payload' => $payload, 'payload_sha256' => hash('sha256', $payload),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Site::query()->create(['name' => 'Activity Site', 'legacy_lead_id' => 'lead-1', 'owner_user_id' => $owner->id]);
    }

    public function test_role_access_and_menu_include_activity_center(): void
    {
        foreach (['super_admin', 'admin', 'manager', 'staff'] as $role) {
            $user = $this->account($role);
            $this->actingAs($user)->get('/crm/admin/activities')->assertOk()
                ->assertSee('บันทึกการติดตามรวม');
            $this->get('/crm/admin')->assertOk()->assertSee('บันทึกการติดตาม');
        }
    }

    public function test_page_renders_summary_filters_timeline_and_shared_detail_write_path(): void
    {
        $staff = $this->account('staff');
        $this->seedLead($staff);

        $this->actingAs($staff)->get('/crm/admin/activities')->assertOk()
            ->assertSee('กิจกรรมและงานติดตามทั้งหมด')
            ->assertSee('งานวันนี้')->assertSee('งานเกินกำหนด')->assertSee('ยังไม่กำหนดวัน')
            ->assertSee('Activity Site')->assertSee('Call decision maker')
            ->assertSee('ประเภทกิจกรรม')->assertSee('ผู้รับผิดชอบ')->assertSee('ช่วงวันที่')
            ->assertSee('/crm/admin/pipeline/lead-1/visits', false)
            ->assertSee('เพิ่มบันทึก');
    }

    public function test_page_validates_filters_and_staff_never_sees_another_users_site(): void
    {
        $staff = $this->account('staff');
        $other = $this->account('staff');
        $this->seedLead($other);

        $this->actingAs($staff)->get('/crm/admin/activities?type=INVALID')->assertSessionHasErrors('type');
        $this->get('/crm/admin/activities')->assertOk()->assertDontSee('Activity Site');
    }
}
