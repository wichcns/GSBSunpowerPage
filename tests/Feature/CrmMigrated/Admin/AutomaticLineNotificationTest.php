<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Actions\SaveSite;
use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Services\CrmLineNotifier;
use App\Crm\Support\SiteEditFingerprint;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class AutomaticLineNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.line_oa.channel_token' => 'token', 'services.line_oa.sending_enabled' => true]);
        DB::table('crm_line_groups')->insert([
            'group_id' => 'Ccentral', 'is_confirmed' => true, 'is_enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_assignment_is_saved_and_failed_line_delivery_is_recorded_without_rolling_back_crm(): void
    {
        Http::fake(['api.line.me/*' => Http::response(['message' => 'temporary failure'], 500)]);
        $this->enable('site_owner_assigned');
        $actor = User::factory()->create();
        $owner = $this->staff('Assigned Staff');
        $site = Site::create(['name' => 'Central Test Site', 'legacy_lead_id' => 'lead-1']);

        $this->actingAs($actor)->post('/crm/admin/ownership/assign', [
            'owner_user_id' => $owner->id,
            'site_ids' => [$site->id],
            'fingerprints' => [$site->id => SiteEditFingerprint::for($site)],
        ])->assertRedirect('/crm/admin/ownership');

        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'owner_user_id' => $owner->id]);
        $this->assertDatabaseHas('crm_notification_outbox', [
            'notification_type' => 'site_owner_assigned', 'status' => 'failed', 'recipient' => 'Ccentral',
        ]);
        Http::assertSentCount(1);
    }

    public function test_line_message_contains_thai_english_and_chinese_in_one_delivery(): void
    {
        Http::fake(['api.line.me/*' => Http::response(['sentMessages' => [['id' => '1']]], 200)]);
        $this->enable('site_owner_assigned');
        $actor = $this->account('manager');
        $owner = $this->staff('Assigned Staff');
        $site = Site::create(['name' => 'Three Language Site', 'legacy_lead_id' => 'lead-i18n']);

        app(CrmLineNotifier::class)->assignment($site, $owner, $actor, 7002);

        $item = DB::table('crm_notification_outbox')->sole();
        $message = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertStringContainsString("[TH]\nมอบหมายงานใหม่", $message);
        $this->assertStringContainsString("[EN]\nNew assignment", $message);
        $this->assertStringContainsString("[中文]\n新任务分配", $message);
        $this->assertStringContainsString('Site: Three Language Site', $message);
        $this->assertStringContainsString('站点： Three Language Site', $message);
        $this->assertDatabaseCount('crm_notification_outbox', 1);
    }

    public function test_sales_stage_change_sends_once_and_an_unchanged_save_does_not_duplicate(): void
    {
        Http::fake(['api.line.me/*' => Http::response(['sentMessages' => [['id' => '1']]], 200)]);
        $this->enable('sales_stage_changed');
        $this->lead(['site_name' => 'Stage Site', 'stage' => 'MOU_SIGNING', 'probability' => 65]);
        $this->actingAs(User::factory()->create());
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');

        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CONTRACT_SIGNING', 'expected_version' => $version])->assertOk();
        $version = $this->getJson('/crm/admin/pipeline/lead-1')->json('opportunity.stage_version');
        $this->patchJson('/crm/admin/pipeline/lead-1/stage', ['stage' => 'CONTRACT_SIGNING', 'expected_version' => $version])->assertOk();

        $this->assertDatabaseCount('crm_notification_outbox', 1);
        $this->assertDatabaseHas('crm_notification_outbox', ['notification_type' => 'sales_stage_changed', 'status' => 'sent']);
        Http::assertSentCount(1);
    }

    public function test_changing_an_existing_site_owner_queues_an_assignment_notification(): void
    {
        $this->enable('site_owner_assigned');
        $actor = User::factory()->create();
        $oldOwner = $this->staff('Old Staff');
        $newOwner = $this->staff('New Staff');
        $site = Site::create(['name' => 'Reassigned Site', 'owner_user_id' => $oldOwner->id]);

        app(SaveSite::class)->update($site, [
            'site_fingerprint' => SiteEditFingerprint::for($site), 'name' => $site->name,
            'owner_user_id' => $newOwner->id,
        ], $actor);

        $this->assertDatabaseHas('crm_sites', ['id' => $site->id, 'owner_user_id' => $newOwner->id]);
        $this->assertDatabaseHas('crm_notification_outbox', ['notification_type' => 'site_owner_assigned', 'status' => 'pending']);
    }

    public function test_starting_construction_sends_owner_and_dates_once_without_a_duplicate_stage_message(): void
    {
        Http::fake(['api.line.me/*' => Http::response(['sentMessages' => [['id' => '1']]], 200)]);
        $this->enable('construction_started');
        $actor = $this->account('super_admin');
        $owner = $this->staff('Construction Staff');
        $version = $this->engineeringProject('SURVEYED');

        $this->actingAs($actor)->postJson('/crm/admin/engineering/lead-1/construction/start', [
            'owner_user_id' => $owner->id,
            'started_on' => '2026-10-27',
            'target_finish_on' => '2026-11-30',
            'expected_project_version' => $version,
        ])->assertOk();

        $this->assertDatabaseCount('crm_notification_outbox', 1);
        $item = DB::table('crm_notification_outbox')->sole();
        $message = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertSame('construction_started', $item->notification_type);
        $this->assertSame('sent', $item->status);
        $this->assertStringContainsString("ผู้รับผิดชอบงานก่อสร้าง: {$owner->name}", $message);
        $this->assertStringContainsString('วันเริ่ม: 27/10/2026', $message);
        $this->assertStringContainsString('คาดว่าจะเสร็จ: 30/11/2026', $message);
        Http::assertSentCount(1);
    }

    public function test_starting_construction_without_target_date_says_it_is_not_specified(): void
    {
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        $this->enable('construction_started');
        $owner = $this->staff('Construction Staff');
        $version = $this->engineeringProject('SURVEYED');

        $this->actingAs($this->account('super_admin'))->postJson('/crm/admin/engineering/lead-1/construction/start', [
            'owner_user_id' => $owner->id,
            'started_on' => '2026-10-27',
            'target_finish_on' => null,
            'expected_project_version' => $version,
        ])->assertOk();

        $item = DB::table('crm_notification_outbox')->sole();
        $message = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertStringContainsString('คาดว่าจะเสร็จ: ยังไม่ระบุ', $message);
    }

    public function test_construction_progress_sends_one_message_and_stale_replay_does_not_duplicate_it(): void
    {
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        $this->travelTo(CarbonImmutable::create(2026, 10, 27, 9, 29, 2, 'Asia/Bangkok'));
        $actor = $this->account('super_admin');
        $owner = $this->staff('Construction Staff');
        $version = $this->engineeringProject('SURVEYED');
        $this->actingAs($actor)->postJson('/crm/admin/engineering/lead-1/construction/start', [
            'owner_user_id' => $owner->id,
            'started_on' => '2026-10-27',
            'expected_project_version' => $version,
        ])->assertOk();
        $this->enable('construction_progress_updated');
        $current = $this->getJson('/crm/admin/engineering/lead-1')->json('construction.version');
        $payload = ['note' => 'ติดตั้งตู้ MDB เรียบร้อยแล้ว', 'expected_version' => $current];

        $this->postJson('/crm/admin/engineering/lead-1/construction/updates', $payload)->assertOk();
        $this->postJson('/crm/admin/engineering/lead-1/construction/updates', $payload)->assertConflict();

        $this->assertDatabaseCount('crm_notification_outbox', 1);
        $item = DB::table('crm_notification_outbox')->sole();
        $message = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertSame('construction_progress_updated', $item->notification_type);
        $this->assertStringContainsString("ผู้รับผิดชอบงานก่อสร้าง: {$owner->name}", $message);
        $this->assertStringContainsString('ความคืบหน้า: ติดตั้งตู้ MDB เรียบร้อยแล้ว', $message);
        $this->assertStringContainsString("บันทึกโดย: {$actor->name}", $message);
        $this->assertStringContainsString('วันที่บันทึก: 27/10/2026 09:29', $message);
        Http::assertSentCount(1);
    }

    public function test_opening_service_sends_construction_details_and_rejects_duplicate_replay(): void
    {
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        $this->enable('service_opened');
        $this->travelTo(CarbonImmutable::create(2026, 10, 27, 10, 15, 0, 'Asia/Bangkok'));
        $actor = $this->account('super_admin');
        $owner = $this->staff('Construction Staff');
        $version = $this->engineeringProject('CONSTRUCTION');
        $this->constructionRecord($owner->id, '2026-10-27', '2026-11-30');
        $payload = ['engineering_stage' => 'LIVE', 'expected_version' => $version];

        $this->actingAs($actor)->patchJson('/crm/admin/engineering/lead-1/stage', $payload)->assertOk();
        $this->patchJson('/crm/admin/engineering/lead-1/stage', $payload)->assertConflict();

        $this->assertDatabaseCount('crm_notification_outbox', 1);
        $item = DB::table('crm_notification_outbox')->sole();
        $message = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertSame('service_opened', $item->notification_type);
        $this->assertStringContainsString("ผู้รับผิดชอบงานก่อสร้าง: {$owner->name}", $message);
        $this->assertStringContainsString('วันเริ่มก่อสร้าง: 27/10/2026', $message);
        $this->assertStringContainsString('วันคาดว่าจะเสร็จ: 30/11/2026', $message);
        $this->assertStringContainsString('วันที่เปิดให้บริการ: 27/10/2026 10:15', $message);
        $this->assertStringContainsString("ผู้ยืนยันเปิดบริการ: {$actor->name}", $message);
        Http::assertSentCount(1);
    }

    public function test_opening_service_without_target_date_says_it_is_not_specified(): void
    {
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        $this->enable('service_opened');
        $actor = $this->account('super_admin');
        $owner = $this->staff('Construction Staff');
        $version = $this->engineeringProject('CONSTRUCTION');
        $this->constructionRecord($owner->id, '2026-10-27', null);

        $this->actingAs($actor)->patchJson('/crm/admin/engineering/lead-1/stage', [
            'engineering_stage' => 'LIVE', 'expected_version' => $version,
        ])->assertOk();

        $item = DB::table('crm_notification_outbox')->sole();
        $message = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertStringContainsString('วันคาดว่าจะเสร็จ: ยังไม่ระบุ', $message);
    }

    public function test_due_command_sends_overdue_and_due_soon_once_each(): void
    {
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        $this->enable('task_due_soon');
        $this->enable('task_overdue');
        $this->travelTo(now()->setTimezone('Asia/Bangkok')->setDate(2026, 10, 26)->startOfDay());
        $this->lead(['site_name' => 'Overdue Site', 'stage' => 'NEGOTIATING', 'assigned_to' => 'Kira', 'next_action' => 'โทรติดตาม', 'next_action_at' => '2026-10-25 09:00:00'], 'late');
        $this->lead(['site_name' => 'Tomorrow Site', 'stage' => 'NEGOTIATING', 'assigned_to' => 'wichTest1', 'next_action' => 'ส่งข้อเสนอ', 'next_action_at' => '2026-10-27 09:00:00'], 'soon', false);

        $this->artisan('crm:line-reminders')->assertSuccessful();
        $this->artisan('crm:line-reminders')->assertSuccessful();

        $this->assertDatabaseCount('crm_notification_outbox', 2);
        $this->assertDatabaseHas('crm_notification_outbox', ['notification_type' => 'task_overdue', 'status' => 'sent']);
        $this->assertDatabaseHas('crm_notification_outbox', ['notification_type' => 'task_due_soon', 'status' => 'sent']);
        $overdue = DB::table('crm_notification_outbox')->where('notification_type', 'task_overdue')->sole();
        $overdueMessage = json_decode($overdue->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertStringContainsString('กำหนด: 25/10/2026 09:00', $overdueMessage);
        $this->assertStringContainsString('เกินกำหนด: 1 วัน', $overdueMessage);
        $this->assertStringContainsString('ขั้นตอนการขาย: ติดต่อแล้ว / อยู่ระหว่างเจรจา', $overdueMessage);
        $this->assertStringContainsString('ผู้รับผิดชอบ: Kira', $overdueMessage);
        $dueSoon = DB::table('crm_notification_outbox')->where('notification_type', 'task_due_soon')->sole();
        $dueSoonMessage = json_decode($dueSoon->payload, true, 512, JSON_THROW_ON_ERROR)['text'];
        $this->assertStringContainsString('กำหนด: 27/10/2026 09:00', $dueSoonMessage);
        $this->assertStringContainsString('ครบกำหนด: พรุ่งนี้', $dueSoonMessage);
        $this->assertStringContainsString('ผู้รับผิดชอบ: wichTest1', $dueSoonMessage);
        Http::assertSentCount(2);
    }

    public function test_super_admin_can_toggle_event_settings_but_staff_cannot(): void
    {
        $super = $this->account('super_admin');
        $this->actingAs($super)->post('/crm/admin/integrations/line/settings', [
            'events' => ['site_owner_assigned', 'service_opened'],
        ])->assertRedirect('/crm/admin/integrations');
        $this->assertDatabaseHas('crm_notification_settings', ['event_type' => 'site_owner_assigned', 'is_enabled' => true]);
        $this->assertDatabaseHas('crm_notification_settings', ['event_type' => 'sales_stage_changed', 'is_enabled' => false]);

        $staff = $this->account('staff');
        $this->actingAs($staff)->post('/crm/admin/integrations/line/settings', ['events' => []])->assertForbidden();
    }

    private function enable(string $event): void
    {
        DB::table('crm_notification_settings')->where('event_type', $event)->update(['is_enabled' => true, 'updated_at' => now()]);
    }

    private function account(string $role): User
    {
        return User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', $role)->value('id')]);
    }

    private function staff(string $name): User
    {
        return User::factory()->create(['name' => $name, 'role_id' => DB::table('crm_roles')->where('slug', 'staff')->value('id')]);
    }

    private function lead(array $payload, string $sourceId = 'lead-1', bool $newBatch = true): void
    {
        $batch = $newBatch || ! DB::table('crm_legacy_import_batches')->exists()
            ? DB::table('crm_legacy_import_batches')->insertGetId([
                'source_sha256' => hash('sha256', $sourceId), 'source_name' => 'line-test-'.$sourceId,
                'exported_at' => '2026-10-26', 'source_counts' => '{}', 'imported_counts' => '{}',
                'validation_report' => '{}', 'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
            ])
            : DB::table('crm_legacy_import_batches')->latest('id')->value('id');
        DB::table('crm_legacy_records')->insert([
            'batch_id' => $batch, 'source_table' => 'leads', 'source_id' => $sourceId,
            'payload' => json_encode($payload), 'payload_sha256' => hash('sha256', $sourceId),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function engineeringProject(string $stage): string
    {
        $this->lead(['site_name' => 'Construction Site', 'stage' => 'SIGNED', 'probability' => 100]);
        $batch = DB::table('crm_legacy_import_batches')->latest('id')->value('id');
        DB::table('crm_engineering_projects')->insert([
            'lead_source_id' => 'lead-1', 'project_code' => 'GSB-CONSTRUCTION-1',
            'engineering_stage' => $stage, 'sync_status' => 'pending_connection',
            'origin' => 'crm', 'version' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return "current:{$batch}:1";
    }

    private function constructionRecord(int $ownerId, string $startedOn, ?string $targetFinishOn): void
    {
        DB::table('crm_engineering_construction_records')->insert([
            'engineering_project_id' => DB::table('crm_engineering_projects')->where('lead_source_id', 'lead-1')->value('id'),
            'lead_source_id' => 'lead-1', 'owner_user_id' => $ownerId,
            'started_on' => $startedOn, 'target_finish_on' => $targetFinishOn,
            'version' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
