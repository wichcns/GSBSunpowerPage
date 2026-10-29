<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\User;
use App\Crm\Services\LineOaGroupChannel;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class LineOaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_invalid_signature_without_recording_a_group(): void
    {
        config(['services.line_oa.channel_secret' => 'secret']);
        $this->postJson('/crm/webhooks/line', ['events' => [['type' => 'join', 'source' => ['type' => 'group', 'groupId' => 'Cbad']]]],
            ['X-Line-Signature' => 'invalid'])->assertForbidden();
        $this->assertDatabaseCount('crm_line_groups', 0);
    }

    public function test_valid_join_webhook_records_candidate_but_does_not_confirm_or_enable_it(): void
    {
        config(['services.line_oa.channel_secret' => 'secret']);
        $payload = json_encode(['events' => [['type' => 'join', 'source' => ['type' => 'group', 'groupId' => 'Ccentral']]]], JSON_THROW_ON_ERROR);
        $signature = base64_encode(hash_hmac('sha256', $payload, 'secret', true));

        $this->call('POST', '/crm/webhooks/line', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_LINE_SIGNATURE' => $signature], $payload)->assertOk();
        $this->assertDatabaseHas('crm_line_groups', ['group_id' => 'Ccentral', 'is_confirmed' => false, 'is_enabled' => false]);
    }

    public function test_super_admin_can_confirm_one_group_and_test_push_only_to_that_group(): void
    {
        Http::fake(['api.line.me/v2/bot/message/push' => Http::response(['sentMessages' => [['id' => '1']]], 200)]);
        config(['services.line_oa.channel_token' => 'token-value', 'services.line_oa.channel_secret' => 'secret',
            'services.line_oa.sending_enabled' => true]);
        DB::table('crm_line_groups')->insert([
            ['group_id' => 'Ccentral', 'is_confirmed' => false, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 'Cother', 'is_confirmed' => false, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $super = $this->account('super_admin');

        $this->actingAs($super)->post(route('crm.admin.integrations.line.confirm'), ['group_id' => 'Ccentral', 'enabled' => '1'])
            ->assertRedirect(route('crm.admin.integrations.index'));
        $this->assertDatabaseHas('crm_line_groups', ['group_id' => 'Ccentral', 'is_confirmed' => true, 'is_enabled' => true]);
        $this->assertDatabaseHas('crm_line_groups', ['group_id' => 'Cother', 'is_confirmed' => false, 'is_enabled' => false]);

        $this->post(route('crm.admin.integrations.line.test'))->assertRedirect(route('crm.admin.integrations.index'));
        Http::assertSent(fn ($request) => $request->url() === 'https://api.line.me/v2/bot/message/push'
            && $request['to'] === 'Ccentral' && str_contains($request['messages'][0]['text'], 'ทดสอบ'));
        $this->assertDatabaseHas('crm_notification_outbox', ['channel' => 'line_group', 'status' => 'sent', 'recipient' => 'Ccentral']);
    }

    public function test_staff_cannot_manage_line_and_no_follower_broadcast_endpoint_is_used(): void
    {
        $staff = $this->account('staff');
        $this->actingAs($staff)->get('/crm/admin/integrations')->assertForbidden();
        $this->post('/crm/admin/integrations/line/confirm', [])->assertForbidden();
        $this->post('/crm/admin/integrations/line/test')->assertForbidden();

        $service = file_get_contents(app_path('Services/LineOaGroupChannel.php')) ?: '';
        $this->assertStringNotContainsString('/broadcast', $service);
        $this->assertStringNotContainsString('/multicast', $service);
        $this->assertStringNotContainsString('/narrowcast', $service);
    }

    public function test_manual_test_sends_its_own_message_even_when_an_older_item_is_pending(): void
    {
        Http::fake(['api.line.me/v2/bot/message/push' => Http::response([], 200)]);
        config(['services.line_oa.channel_token' => 'token-value', 'services.line_oa.sending_enabled' => true]);
        DB::table('crm_line_groups')->insert([
            'group_id' => 'Ccentral', 'is_confirmed' => true, 'is_enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('crm_notification_outbox')->insert([
            'event_key' => 'old-item', 'channel' => 'line_group', 'notification_type' => 'old',
            'payload' => json_encode(['text' => 'ข้อความเก่า']), 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->account('super_admin'))->post(route('crm.admin.integrations.line.test'))->assertSessionHas('status');

        $this->assertDatabaseHas('crm_notification_outbox', ['notification_type' => 'TEST', 'status' => 'sent']);
        $this->assertDatabaseHas('crm_notification_outbox', ['event_key' => 'old-item', 'status' => 'pending']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request['messages'][0]['text'], 'ทดสอบ'));
    }

    public function test_failed_delivery_retries_after_delay_with_the_same_line_retry_key(): void
    {
        $this->readyDestination();
        $this->travelTo(CarbonImmutable::create(2026, 10, 27, 10, 0, 0, 'Asia/Bangkok'));
        Http::fake(['api.line.me/*' => Http::sequence()->push(['message' => 'temporary'], 500)->push([], 200)]);
        $channel = app(LineOaGroupChannel::class);

        $this->assertFalse($channel->send('demo', 'retry-event', 'hello'));
        $failed = DB::table('crm_notification_outbox')->where('event_key', 'retry-event')->first();
        $this->assertSame('failed', $failed->status);
        $this->assertSame(1, $failed->attempts);
        $this->assertNotNull($failed->retry_key);
        $this->assertSame('2026-10-27 03:05:00', $failed->next_attempt_at);
        $this->assertSame(0, $channel->dispatchPending());
        Http::assertSentCount(1);

        $this->travel(5)->minutes();
        $this->assertSame(1, $channel->dispatchPending());
        $sent = DB::table('crm_notification_outbox')->where('event_key', 'retry-event')->first();
        $this->assertSame('sent', $sent->status);
        $this->assertSame(2, $sent->attempts);
        $requests = Http::recorded();
        $this->assertSame($requests[0][0]->header('X-Line-Retry-Key'), $requests[1][0]->header('X-Line-Retry-Key'));
    }

    public function test_delivery_stops_after_three_failed_attempts(): void
    {
        $this->readyDestination();
        $this->travelTo(CarbonImmutable::create(2026, 10, 27, 10, 0, 0, 'Asia/Bangkok'));
        Http::fake(['api.line.me/*' => Http::response(['message' => 'down'], 500)]);
        $channel = app(LineOaGroupChannel::class);

        $channel->send('demo', 'exhaust-event', 'hello');
        $this->travel(5)->minutes();
        $channel->dispatchPending();
        $this->travel(15)->minutes();
        $channel->dispatchPending();
        $this->travel(60)->minutes();
        $channel->dispatchPending();

        $this->assertDatabaseHas('crm_notification_outbox', [
            'event_key' => 'exhaust-event', 'status' => 'exhausted', 'attempts' => 3,
        ]);
        Http::assertSentCount(3);
    }

    public function test_manual_test_without_a_ready_destination_does_not_create_backlog(): void
    {
        config(['services.line_oa.channel_token' => 'token-value', 'services.line_oa.sending_enabled' => false]);

        $this->assertFalse(app(LineOaGroupChannel::class)->sendTest());
        $this->assertDatabaseCount('crm_notification_outbox', 0);
    }

    public function test_stale_processing_item_is_recovered_and_sent(): void
    {
        $this->readyDestination();
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        DB::table('crm_notification_outbox')->insert([
            'event_key' => 'stale-event', 'channel' => 'line_group', 'notification_type' => 'demo',
            'payload' => json_encode(['text' => 'stale']), 'status' => 'processing', 'attempts' => 1,
            'retry_key' => (string) \Illuminate\Support\Str::uuid(), 'processing_at' => now()->subMinutes(11),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertSame(1, app(LineOaGroupChannel::class)->dispatchPending());
        $this->assertDatabaseHas('crm_notification_outbox', [
            'event_key' => 'stale-event', 'status' => 'sent', 'attempts' => 2,
        ]);
    }

    public function test_super_admin_can_manually_retry_an_exhausted_item(): void
    {
        $this->readyDestination();
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        $id = DB::table('crm_notification_outbox')->insertGetId([
            'event_key' => 'manual-retry', 'channel' => 'line_group', 'notification_type' => 'demo',
            'payload' => json_encode(['text' => 'retry me']), 'status' => 'exhausted', 'attempts' => 3,
            'retry_key' => (string) \Illuminate\Support\Str::uuid(), 'last_error' => 'down',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->account('super_admin'));
        $this->get(route('crm.admin.integrations.index'))
            ->assertOk()->assertSee('หยุดลองอัตโนมัติ')->assertSee('ครั้งที่ 3/3')->assertSee('ลองส่งใหม่');

        $this
            ->post("/crm/admin/integrations/line/outbox/{$id}/retry")
            ->assertRedirect(route('crm.admin.integrations.index'));

        $this->assertDatabaseHas('crm_notification_outbox', [
            'id' => $id, 'status' => 'sent', 'attempts' => 1, 'last_error' => null,
        ]);
        Http::assertSentCount(1);
        $this->assertSame(0, app(LineOaGroupChannel::class)->dispatchPending());
        Http::assertSentCount(1);
    }

    public function test_staff_cannot_manually_retry_an_outbox_item(): void
    {
        $id = DB::table('crm_notification_outbox')->insertGetId([
            'event_key' => 'forbidden-retry', 'channel' => 'line_group', 'notification_type' => 'demo',
            'payload' => json_encode(['text' => 'retry me']), 'status' => 'failed', 'attempts' => 1,
            'retry_key' => (string) \Illuminate\Support\Str::uuid(), 'next_attempt_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->account('staff'))
            ->post("/crm/admin/integrations/line/outbox/{$id}/retry")
            ->assertForbidden();
        $this->assertDatabaseHas('crm_notification_outbox', ['id' => $id, 'status' => 'failed', 'attempts' => 1]);
    }

    public function test_line_outbox_command_dispatches_due_items(): void
    {
        $this->readyDestination();
        Http::fake(['api.line.me/*' => Http::response([], 200)]);
        DB::table('crm_notification_outbox')->insert([
            'event_key' => 'command-event', 'channel' => 'line_group', 'notification_type' => 'demo',
            'payload' => json_encode(['text' => 'command']), 'status' => 'pending', 'attempts' => 0,
            'retry_key' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('crm:line-outbox')->assertSuccessful();
        $this->assertDatabaseHas('crm_notification_outbox', ['event_key' => 'command-event', 'status' => 'sent']);
    }

    private function readyDestination(): void
    {
        config(['services.line_oa.channel_token' => 'token-value', 'services.line_oa.sending_enabled' => true]);
        DB::table('crm_line_groups')->insert([
            'group_id' => 'Ccentral', 'is_confirmed' => true, 'is_enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function account(string $role): User
    {
        return User::factory()->create(['role_id' => DB::table('crm_roles')->where('slug', $role)->value('id')]);
    }
}
