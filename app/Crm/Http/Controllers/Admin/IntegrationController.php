<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Services\LineOaGroupChannel;
use App\Crm\Support\LineNotificationEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        return view('crm.admin.integrations.index', [
            'title' => __('integrations.title'),
            'groups' => Schema::hasTable('crm_line_groups') ? DB::table('crm_line_groups')->latest('last_seen_at')->get() : collect(),
            'outbox' => Schema::hasTable('crm_notification_outbox') ? DB::table('crm_notification_outbox')->latest('id')->limit(20)->get() : collect(),
            'lineReady' => config('services.line_oa.channel_token') && config('services.line_oa.channel_secret'),
            'sendingEnabled' => (bool) config('services.line_oa.sending_enabled'),
            'eventLabels' => collect(array_keys(LineNotificationEvents::LABELS))
                ->mapWithKeys(fn (string $event): array => [$event => __("integrations.events.{$event}")])
                ->all(),
            'notificationSettings' => Schema::hasTable('crm_notification_settings')
                ? DB::table('crm_notification_settings')->pluck('is_enabled', 'event_type') : collect(),
        ]);
    }

    public function confirmLineGroup(Request $request): RedirectResponse
    {
        $data = $request->validate(['group_id' => ['required', 'string', 'max:64', 'exists:crm_line_groups,group_id'], 'enabled' => ['nullable', 'boolean']]);
        DB::transaction(function () use ($data, $request): void {
            DB::table('crm_line_groups')->update(['is_confirmed' => false, 'is_enabled' => false, 'confirmed_by_user_id' => null, 'confirmed_at' => null]);
            DB::table('crm_line_groups')->where('group_id', $data['group_id'])->update([
                'is_confirmed' => true, 'is_enabled' => (bool) ($data['enabled'] ?? false),
                'confirmed_by_user_id' => $request->user()->id, 'confirmed_at' => now(), 'updated_at' => now(),
            ]);
        });

        return redirect()->route('crm.admin.integrations.index')->with('status', __('integrations.messages.group_confirmed'));
    }

    public function testLine(LineOaGroupChannel $line): RedirectResponse
    {
        $sent = $line->sendTest();

        return redirect()->route('crm.admin.integrations.index')->with($sent ? 'status' : 'error',
            $sent ? __('integrations.messages.test_sent') : __('integrations.messages.test_failed'));
    }

    public function updateLineSettings(Request $request): RedirectResponse
    {
        $data = $request->validate(['events' => ['nullable', 'array'], 'events.*' => ['string', 'in:'.implode(',', array_keys(LineNotificationEvents::LABELS))]]);
        $enabled = array_flip($data['events'] ?? []);
        DB::transaction(function () use ($enabled): void {
            foreach (LineNotificationEvents::LABELS as $event => $_label) {
                DB::table('crm_notification_settings')->where('event_type', $event)->update([
                    'is_enabled' => isset($enabled[$event]), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('crm.admin.integrations.index')->with('status', __('integrations.messages.settings_saved'));
    }

    public function retryLineOutbox(int $outbox, LineOaGroupChannel $line): RedirectResponse
    {
        $sent = $line->retry($outbox);

        return redirect()->route('crm.admin.integrations.index')->with($sent ? 'status' : 'error',
            $sent ? __('integrations.messages.retry_sent') : __('integrations.messages.retry_failed'));
    }
}
