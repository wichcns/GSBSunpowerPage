<?php

namespace App\Crm\Console\Commands;

use App\Crm\Services\LineOaGroupChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchLineOutbox extends Command
{
    protected $signature = 'crm:line-outbox {--limit=20 : จำนวนรายการสูงสุดต่อรอบ}';

    protected $description = 'ส่งรายการแจ้งเตือน LINE ที่พร้อมส่งหรือครบเวลาลองใหม่';

    public function handle(LineOaGroupChannel $line): int
    {
        if (config('database.crm_enabled')) {
            DB::setDefaultConnection('crm');
        }

        $limit = max(1, min(100, (int) $this->option('limit')));
        $sent = $line->dispatchPending($limit);
        $this->info("ส่ง LINE สำเร็จ {$sent} รายการ");

        return self::SUCCESS;
    }
}
