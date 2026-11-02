<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Services\CrmLineNotifier;
use App\Crm\Services\CrmNotificationCenter;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class InAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_between_three_supported_languages_and_preference_persists(): void
    {
        $user = $this->account('staff', 'Locale User');

        $this->actingAs($user)->post('/crm/admin/locale', ['locale' => 'zh'])->assertRedirect();
        $this->assertDatabaseHas('crm_users', ['id' => $user->id, 'locale' => 'zh']);
        $this->get('/crm/admin')->assertOk()->assertSee('运营工作区')->assertSee('通知');

        $this->post('/crm/admin/locale', ['locale' => 'en'])->assertRedirect();
        $this->get('/crm/admin')->assertOk()->assertSee('Operations workspace')->assertSee('Notifications');
        $this->post('/crm/admin/locale', ['locale' => 'jp'])->assertSessionHasErrors('locale');
    }

    public function test_assignment_creates_one_personal_notification_even_when_line_is_disabled(): void
    {
        config(['services.line_oa.sending_enabled' => false]);
        $actor = $this->account('manager', 'Manager');
        $owner = $this->account('staff', 'Assigned Staff');
        $site = Site::create(['name' => 'Notification Site', 'legacy_lead_id' => 'lead-1']);

        $payload = [
            'owner_user_id' => $owner->id, 'site_ids' => [$site->id],
            'fingerprints' => [$site->id => SiteEditFingerprint::for($site)],
        ];
        $this->actingAs($actor)->post('/crm/admin/ownership/assign', $payload)->assertRedirect('/crm/admin/ownership');
        $auditId = DB::table('crm_site_audits')->where('site_id', $site->id)->where('action', 'owner_assigned')->value('id');
        app(CrmLineNotifier::class)->assignment($site->refresh(), $owner, $actor, $auditId);

        $this->assertDatabaseCount('crm_notifications', 1);
        $this->assertDatabaseHas('crm_notifications', [
            'user_id' => $owner->id, 'type' => 'site_owner_assigned',
            'title' => 'มอบหมายงานใหม่', 'read_at' => null,
        ]);
        Http::assertNothingSent();
    }

    public function test_assignment_stores_three_languages_and_uses_the_recipients_locale(): void
    {
        config(['services.line_oa.sending_enabled' => false]);
        $actor = $this->account('manager', 'Manager');
        $owner = $this->account('staff', 'Assigned Staff');
        $owner->update(['locale' => 'zh']);
        $site = Site::create(['name' => 'Three Language Site', 'legacy_lead_id' => 'lead-i18n']);

        app(CrmLineNotifier::class)->assignment($site, $owner, $actor, 7001);

        $notification = DB::table('crm_notifications')->sole();
        $translations = json_decode($notification->translations, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('มอบหมายงานใหม่', $translations['th']['title']);
        $this->assertSame('New assignment', $translations['en']['title']);
        $this->assertSame('新任务分配', $translations['zh']['title']);
        $this->assertStringContainsString('站点： Three Language Site', $translations['zh']['body']);

        $this->actingAs($owner)->get('/crm/admin/notifications')
            ->assertOk()
            ->assertSee('新任务分配')
            ->assertSee('站点： Three Language Site')
            ->assertDontSee('มอบหมายงานใหม่');
    }

    public function test_legacy_owner_label_without_a_linked_user_does_not_receive_a_notification(): void
    {
        $this->account('staff', 'Legacy Name');
        Site::create(['name' => 'Legacy Only', 'legacy_lead_id' => 'lead-legacy', 'legacy_owner_label' => 'Legacy Name']);

        app(CrmNotificationCenter::class)->notifyForLead(
            'lead-legacy', 'legacy-event', 'task_due_soon', 'ใกล้ครบกำหนด', 'ทดสอบ', '/crm/admin/my-work'
        );

        $this->assertDatabaseCount('crm_notifications', 0);
    }

    public function test_sales_and_engineering_events_reach_only_linked_accounts_without_duplicates(): void
    {
        $siteOwner = $this->account('staff', 'Site Owner');
        $surveyOwner = $this->account('staff', 'Survey Owner');
        $constructionOwner = $this->account('staff', 'Construction Owner');
        Site::create(['name' => 'Linked Site', 'legacy_lead_id' => 'lead-linked', 'owner_user_id' => $siteOwner->id]);
        $projectId = DB::table('crm_engineering_projects')->insertGetId([
            'lead_source_id' => 'lead-linked', 'project_code' => 'NOTIFY-1', 'engineering_stage' => 'AWAITING_SURVEY',
            'sync_status' => 'pending_connection', 'origin' => 'crm', 'version' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_survey_tasks')->insert([
            'engineering_project_id' => $projectId, 'lead_source_id' => 'lead-linked',
            'assignee_user_id' => $surveyOwner->id, 'status' => 'scheduled',
            'version' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_engineering_construction_records')->insert([
            'engineering_project_id' => $projectId, 'lead_source_id' => 'lead-linked',
            'owner_user_id' => $constructionOwner->id, 'started_on' => '2026-10-27',
            'version' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $notifier = app(CrmLineNotifier::class);

        $notifier->salesStage('lead-linked', 'Linked Site', 'MOU_SIGNING', 'CONTRACT_SIGNING', $siteOwner->id, 10);
        $notifier->engineeringStage('lead-linked', 'Linked Site', 'AWAITING_SURVEY', 'SURVEYED', $siteOwner->id, 20);
        $notifier->engineeringStage('lead-linked', 'Linked Site', 'AWAITING_SURVEY', 'SURVEYED', $siteOwner->id, 20);

        $this->assertDatabaseHas('crm_notifications', ['user_id' => $siteOwner->id, 'event_key' => 'sales-stage:10']);
        $body = DB::table('crm_notifications')->where('user_id', $siteOwner->id)->where('event_key', 'sales-stage:10')->value('body');
        $this->assertStringContainsString('Cooperation contract signing', (string) $body);
        $this->assertDatabaseHas('crm_notifications', ['user_id' => $surveyOwner->id, 'event_key' => 'engineering-stage:20']);
        $this->assertDatabaseHas('crm_notifications', ['user_id' => $constructionOwner->id, 'event_key' => 'engineering-stage:20']);
        $this->assertSame(4, DB::table('crm_notifications')->count());
    }

    public function test_topbar_and_history_show_only_the_authenticated_users_notifications(): void
    {
        $owner = $this->account('staff', 'Owner');
        $other = $this->account('staff', 'Other');
        $center = app(CrmNotificationCenter::class);
        $center->notify([$owner->id], 'event-owner', 'demo', 'งานของ Owner', 'รายละเอียด', '/crm/admin/my-work');
        $center->notify([$other->id], 'event-other', 'demo', 'งานของ Other', 'ความลับ', '/crm/admin/my-work');

        $this->actingAs($owner)->get('/crm/admin')->assertOk()->assertSee('งานของ Owner')->assertDontSee('งานของ Other');
        $this->get('/crm/admin/notifications')->assertOk()->assertSee('งานของ Owner')->assertDontSee('งานของ Other');
    }

    public function test_user_can_open_one_notification_and_mark_all_their_notifications_as_read(): void
    {
        $owner = $this->account('staff', 'Owner');
        $other = $this->account('staff', 'Other');
        $center = app(CrmNotificationCenter::class);
        $center->notify([$owner->id], 'event-one', 'demo', 'รายการหนึ่ง', 'รายละเอียด', '/crm/admin/my-work');
        $center->notify([$owner->id], 'event-two', 'demo', 'รายการสอง', 'รายละเอียด', '/crm/admin/my-work');
        $center->notify([$other->id], 'event-other', 'demo', 'ของคนอื่น', 'รายละเอียด', '/crm/admin/my-work');
        $first = DB::table('crm_notifications')->where('user_id', $owner->id)->oldest('id')->first();

        $this->actingAs($owner)->post("/crm/admin/notifications/{$first->id}/open")
            ->assertRedirect('/crm/admin/my-work');
        $this->assertDatabaseMissing('crm_notifications', ['id' => $first->id, 'read_at' => null]);

        $this->post('/crm/admin/notifications/read-all')->assertRedirect();
        $this->assertSame(0, DB::table('crm_notifications')->where('user_id', $owner->id)->whereNull('read_at')->count());
        $this->assertSame(1, DB::table('crm_notifications')->where('user_id', $other->id)->whereNull('read_at')->count());
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $owner = $this->account('staff', 'Owner');
        $other = $this->account('staff', 'Other');
        app(CrmNotificationCenter::class)->notify([$other->id], 'private-event', 'demo', 'Private', 'Secret', '/crm/admin/my-work');
        $id = DB::table('crm_notifications')->where('user_id', $other->id)->value('id');

        $this->actingAs($owner)->post("/crm/admin/notifications/{$id}/open")->assertNotFound();
        $this->assertDatabaseHas('crm_notifications', ['id' => $id, 'read_at' => null]);
    }

    private function account(string $role, string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role_id' => DB::table('crm_roles')->where('slug', $role)->value('id'),
        ]);
    }
}
