<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class TeamWorkTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role, string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role_id' => DB::table('crm_roles')->where('slug', $role)->value('id'),
        ]);
    }

    public function test_manager_sees_staff_assignments_but_not_other_roles_and_can_filter_staff(): void
    {
        $manager = $this->account('manager', 'Manager One');
        $first = $this->account('staff', 'Staff Alpha');
        $second = $this->account('staff', 'Staff Beta');
        $admin = $this->account('admin', 'Admin One');
        $batch = DB::table('crm_legacy_import_batches')->insertGetId([
            'source_sha256' => str_repeat('a', 64), 'source_name' => 'team-test', 'exported_at' => '2026-10-22',
            'source_counts' => '{}', 'imported_counts' => '{}', 'validation_report' => '{}',
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => 'alpha-lead',
            'payload' => json_encode(['site_name' => 'Alpha Site', 'stage' => 'NEGOTIATING', 'next_action' => 'Call Alpha']),
            'payload_sha256' => str_repeat('b', 64), 'created_at' => now(), 'updated_at' => now(),
        ]);
        Site::create(['name' => 'Alpha Site', 'owner_user_id' => $first->id, 'legacy_lead_id' => 'alpha-lead']);
        Site::create(['name' => 'Beta Site', 'owner_user_id' => $second->id]);
        Site::create(['name' => 'Admin Site', 'owner_user_id' => $admin->id]);

        $this->actingAs($manager)->get('/crm/admin/team-work')->assertOk()
            ->assertSee('งานของทีม')->assertSee('Staff Alpha')->assertSee('Alpha Site')
            ->assertSee('Staff Beta')->assertSee('Beta Site')->assertSee('Call Alpha')->assertDontSee('Admin Site')
            ->assertSee('Staff ทุกคน');
        $this->get('/crm/admin/team-work?staff='.$first->id)->assertOk()
            ->assertSee('Alpha Site')->assertDontSee('Beta Site');
        $this->get('/crm/admin/my-work')->assertForbidden();
        $this->get('/crm/admin')->assertOk()->assertSee('งานของทีม')->assertDontSee('งานของฉัน');
    }

    public function test_staff_cannot_open_team_work_or_see_its_menu(): void
    {
        $staff = $this->account('staff', 'Staff One');
        $this->actingAs($staff)->get('/crm/admin/team-work')->assertForbidden();
        $this->get('/crm/admin')->assertOk()->assertSee('งานของฉัน')
            ->assertDontSee('href="http://localhost/crm/admin/team-work"', false);
    }
}
