<?php

namespace App\Crm\Console\Commands;

use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\Services\CrmLineNotifier;
use App\Crm\Services\LineOaGroupChannel;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendLineReminders extends Command
{
    protected $signature = 'crm:line-reminders';

    protected $description = 'Queue and deliver due CRM work notifications to the confirmed LINE group';

    public function handle(LegacyPipeline $pipeline, CrmLineNotifier $notifier, LineOaGroupChannel $channel): int
    {
        if (config('database.crm_enabled')) {
            DB::setDefaultConnection('crm');
        }
        app()->setLocale('th');

        $today = CarbonImmutable::now('Asia/Bangkok')->startOfDay();
        foreach ($pipeline->all() as $lead) {
            if ($lead['stage_code'] === 'CANCELLED' || $lead['next_action_raw'] === '' || $lead['next_action_at_raw'] === '') {
                continue;
            }
            try {
                $due = CarbonImmutable::parse($lead['next_action_at_raw'], 'Asia/Bangkok')->startOfDay();
            } catch (\Throwable) {
                continue;
            }
            if ($due->lessThan($today)) {
                $notifier->task('task_overdue', $lead, $today->toDateString());
            } elseif ($due->equalTo($today->addDay())) {
                $notifier->task('task_due_soon', $lead, $today->toDateString());
            }
        }
        $channel->dispatchPending();
        $this->info('ตรวจสอบและจัดคิวแจ้งเตือนงานครบกำหนดแล้ว');

        return self::SUCCESS;
    }
}
