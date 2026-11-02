<?php

namespace App\Crm\Services;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\Support\EngineeringStages;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CrmLineNotifier
{
    public function __construct(
        private readonly LineOaGroupChannel $channel,
        private readonly CrmNotificationCenter $notifications,
    ) {}

    public function assignment(Site $site, User $owner, User $actor, int $auditId): void
    {
        $url = '/crm/admin/my-work';
        $this->queue('site_owner_assigned', "site-owner:{$auditId}", [
            'มอบหมายงานใหม่', "สถานที่: {$site->name}", "ผู้รับผิดชอบ: {$owner->name}",
            "ผู้มอบหมาย: {$actor->name}", 'เปิด CRM: '.url($url),
        ], [$owner->id], $url);
    }

    public function salesStage(string $sourceId, string $siteName, string $from, string $to, int $actorId, int $historyId): void
    {
        $actor = User::find($actorId)?->name ?? 'ไม่ทราบผู้ดำเนินการ';
        $url = '/crm/admin/pipeline?lead='.urlencode($sourceId);
        $this->queue('sales_stage_changed', "sales-stage:{$historyId}", [
            'เปลี่ยนขั้นตอนการขาย', "สถานที่: {$siteName}",
            'จาก: '.LegacyPipeline::label($from),
            'เป็น: '.LegacyPipeline::label($to), "ผู้ดำเนินการ: {$actor}",
            'เปิด CRM: '.url($url),
        ], $this->notifications->recipientIdsForLead($sourceId), $url);
    }

    public function engineeringStage(string $sourceId, string $siteName, string $from, string $to, int $actorId, int $historyId): void
    {
        $type = match ($to) {
            'CONSTRUCTION' => 'construction_started', 'LIVE' => 'service_opened', default => 'engineering_stage_changed'
        };
        $actor = User::find($actorId)?->name ?? 'ไม่ทราบผู้ดำเนินการ';
        if ($type === 'service_opened') {
            $this->serviceOpened($sourceId, $siteName, $actor, $historyId);

            return;
        }
        $url = '/crm/admin/engineering?project='.urlencode($sourceId);
        $this->queue($type, "engineering-stage:{$historyId}", [
            match ($type) {
                'construction_started' => 'เริ่มงานก่อสร้าง', 'service_opened' => 'เปิดให้บริการสำเร็จ', default => 'เปลี่ยนขั้นวิศวกรรม'
            },
            "สถานที่: {$siteName}", 'จาก: '.EngineeringStages::label($from),
            'เป็น: '.EngineeringStages::label($to), "ผู้ดำเนินการ: {$actor}",
            'เปิด CRM: '.url($url),
        ], $this->notifications->recipientIdsForEngineering($sourceId), $url);
    }

    private function serviceOpened(string $sourceId, string $siteName, string $actor, int $historyId): void
    {
        $construction = DB::table('crm_engineering_construction_records')
            ->where('lead_source_id', $sourceId)->first();
        $owner = $construction === null
            ? 'ยังไม่ระบุ'
            : (User::find($construction->owner_user_id)?->name ?? 'ไม่ทราบผู้รับผิดชอบ');
        $startedOn = $construction?->started_on
            ? $this->date($construction->started_on)
            : 'ยังไม่ระบุ';
        $targetFinishOn = $construction?->target_finish_on
            ? $this->date($construction->target_finish_on)
            : 'ยังไม่ระบุ';

        $url = '/crm/admin/engineering?project='.urlencode($sourceId);
        $this->queue('service_opened', "engineering-stage:{$historyId}", [
            'เปิดให้บริการสำเร็จ', "สถานที่: {$siteName}",
            "ผู้รับผิดชอบงานก่อสร้าง: {$owner}", "วันเริ่มก่อสร้าง: {$startedOn}",
            "วันคาดว่าจะเสร็จ: {$targetFinishOn}",
            'วันที่เปิดให้บริการ: '.CarbonImmutable::now('Asia/Bangkok')->format('d/m/Y H:i'),
            "ผู้ยืนยันเปิดบริการ: {$actor}",
            'เปิด CRM: '.url($url),
        ], $construction?->owner_user_id ? [(int) $construction->owner_user_id] : [], $url);
    }

    public function constructionStarted(
        string $sourceId,
        string $siteName,
        int $ownerId,
        string $startedOn,
        ?string $targetFinishOn,
        int $actorId,
        int $recordId,
    ): void {
        $owner = User::find($ownerId)?->name ?? 'ไม่ทราบผู้รับผิดชอบ';
        $actor = User::find($actorId)?->name ?? 'ไม่ทราบผู้ดำเนินการ';
        $url = '/crm/admin/engineering?project='.urlencode($sourceId);
        $this->queue('construction_started', "construction-started:{$recordId}", [
            'เริ่มงานก่อสร้าง', "สถานที่: {$siteName}", "ผู้รับผิดชอบงานก่อสร้าง: {$owner}",
            'วันเริ่ม: '.$this->date($startedOn),
            'คาดว่าจะเสร็จ: '.($targetFinishOn ? $this->date($targetFinishOn) : 'ยังไม่ระบุ'),
            "ผู้ดำเนินการ: {$actor}",
            'เปิด CRM: '.url($url),
        ], [$ownerId], $url);
    }

    public function constructionProgress(
        string $sourceId,
        string $siteName,
        int $ownerId,
        string $note,
        int $actorId,
        int $updateId,
    ): void {
        $owner = User::find($ownerId)?->name ?? 'ไม่ทราบผู้รับผิดชอบ';
        $actor = User::find($actorId)?->name ?? 'ไม่ทราบผู้ดำเนินการ';
        $url = '/crm/admin/engineering?project='.urlencode($sourceId);
        $this->queue('construction_progress_updated', "construction-progress:{$updateId}", [
            'อัปเดตความคืบหน้างานก่อสร้าง', "สถานที่: {$siteName}",
            "ผู้รับผิดชอบงานก่อสร้าง: {$owner}", "ความคืบหน้า: {$note}",
            "บันทึกโดย: {$actor}", 'วันที่บันทึก: '.CarbonImmutable::now('Asia/Bangkok')->format('d/m/Y H:i'),
            'เปิด CRM: '.url($url),
        ], [$ownerId], $url);
    }

    public function task(string $type, array $lead, string $dateKey): void
    {
        $due = CarbonImmutable::parse($lead['next_action_at_raw'], 'Asia/Bangkok')->setTimezone('Asia/Bangkok');
        $timing = $type === 'task_overdue'
            ? 'เกินกำหนด: '.(int) $due->startOfDay()->diffInDays(CarbonImmutable::now('Asia/Bangkok')->startOfDay()).' วัน'
            : 'ครบกำหนด: พรุ่งนี้';
        $url = '/crm/admin/pipeline?lead='.urlencode($lead['source_id']);
        $this->queue($type, "{$type}:{$lead['source_id']}:{$dateKey}", [
            $type === 'task_overdue' ? 'งานเกินกำหนด' : 'งานใกล้ครบกำหนด',
            "สถานที่: {$lead['site_name']}", "งานถัดไป: {$lead['next_action_raw']}",
            'กำหนด: '.$due->format('d/m/Y H:i'), $timing,
            "ขั้นตอนการขาย: {$lead['stage_label']}", "ผู้รับผิดชอบ: {$lead['owner']}",
            'เปิด CRM: '.url($url),
        ], $this->notifications->recipientIdsForLead($lead['source_id']), $url);
    }

    private function queue(string $type, string $eventKey, array $lines, iterable $recipientIds, string $actionUrl): void
    {
        $bodyLines = array_values(array_filter(array_slice($lines, 1), fn (string $line): bool => ! str_starts_with($line, 'เปิด CRM:')));
        $translations = $this->translations($lines[0], $bodyLines);
        $this->notifications->notify($recipientIds, $eventKey, $type, $lines[0], implode("\n", $bodyLines), $actionUrl, $translations);

        $enabled = DB::table('crm_notification_settings')->where('event_type', $type)->value('is_enabled');
        if (! $enabled) {
            return;
        }
        $lineSections = [];
        foreach (['th' => 'TH', 'en' => 'EN', 'zh' => '中文'] as $locale => $label) {
            $lineSections[] = "[{$label}]\n".$translations[$locale]['title']."\n".$translations[$locale]['body'];
        }
        $lineSections[] = end($lines);
        $this->channel->queue($type, $eventKey, "GSB EV CRM\n".implode("\n\n", $lineSections));
    }

    private function translations(string $title, array $bodyLines): array
    {
        return collect(['th', 'en', 'zh'])->mapWithKeys(fn (string $locale): array => [$locale => [
            'title' => $this->translateText($title, $locale),
            'body' => implode("\n", array_map(fn (string $line): string => $this->translateText($line, $locale), $bodyLines)),
        ]])->all();
    }

    private function translateText(string $text, string $locale): string
    {
        if ($locale === 'th') {
            return $text;
        }
        $exact = [
            'en' => ['มอบหมายงานใหม่' => 'New assignment', 'เปลี่ยนขั้นตอนการขาย' => 'Sales stage changed', 'เปลี่ยนขั้นวิศวกรรม' => 'Engineering stage changed', 'เริ่มงานก่อสร้าง' => 'Construction started', 'อัปเดตความคืบหน้างานก่อสร้าง' => 'Construction progress updated', 'เปิดให้บริการสำเร็จ' => 'Service opened', 'งานเกินกำหนด' => 'Overdue task', 'งานใกล้ครบกำหนด' => 'Task due soon', 'ยังไม่ระบุ' => 'Not specified', 'ครบกำหนด: พรุ่งนี้' => 'Due: tomorrow'],
            'zh' => ['มอบหมายงานใหม่' => '新任务分配', 'เปลี่ยนขั้นตอนการขาย' => '销售阶段已更改', 'เปลี่ยนขั้นวิศวกรรม' => '工程阶段已更改', 'เริ่มงานก่อสร้าง' => '施工已开始', 'อัปเดตความคืบหน้างานก่อสร้าง' => '施工进度已更新', 'เปิดให้บริการสำเร็จ' => '已投入运营', 'งานเกินกำหนด' => '任务已逾期', 'งานใกล้ครบกำหนด' => '任务即将到期', 'ยังไม่ระบุ' => '未指定', 'ครบกำหนด: พรุ่งนี้' => '到期：明天'],
        ][$locale];
        if (isset($exact[$text])) {
            return $exact[$text];
        }
        $prefixes = [
            'en' => ['สถานที่:' => 'Site:', 'ผู้รับผิดชอบ:' => 'Assignee:', 'ผู้มอบหมาย:' => 'Assigned by:', 'จาก:' => 'From:', 'เป็น:' => 'To:', 'ผู้ดำเนินการ:' => 'Performed by:', 'ผู้รับผิดชอบงานก่อสร้าง:' => 'Construction owner:', 'วันเริ่มก่อสร้าง:' => 'Construction start:', 'วันเริ่ม:' => 'Start date:', 'วันคาดว่าจะเสร็จ:' => 'Expected completion:', 'คาดว่าจะเสร็จ:' => 'Expected completion:', 'วันที่เปิดให้บริการ:' => 'Service opening date:', 'ผู้ยืนยันเปิดบริการ:' => 'Service confirmed by:', 'ความคืบหน้า:' => 'Progress:', 'บันทึกโดย:' => 'Recorded by:', 'วันที่บันทึก:' => 'Recorded at:', 'งานถัดไป:' => 'Next task:', 'กำหนด:' => 'Due:', 'เกินกำหนด:' => 'Overdue:', 'ขั้นตอนการขาย:' => 'Sales stage:'],
            'zh' => ['สถานที่:' => '站点：', 'ผู้รับผิดชอบ:' => '负责人：', 'ผู้มอบหมาย:' => '分配人：', 'จาก:' => '从：', 'เป็น:' => '至：', 'ผู้ดำเนินการ:' => '操作人：', 'ผู้รับผิดชอบงานก่อสร้าง:' => '施工负责人：', 'วันเริ่มก่อสร้าง:' => '施工开始日期：', 'วันเริ่ม:' => '开始日期：', 'วันคาดว่าจะเสร็จ:' => '预计完成日期：', 'คาดว่าจะเสร็จ:' => '预计完成日期：', 'วันที่เปิดให้บริการ:' => '投入运营日期：', 'ผู้ยืนยันเปิดบริการ:' => '确认人：', 'ความคืบหน้า:' => '进度：', 'บันทึกโดย:' => '记录人：', 'วันที่บันทึก:' => '记录时间：', 'งานถัดไป:' => '下一任务：', 'กำหนด:' => '截止时间：', 'เกินกำหนด:' => '逾期：', 'ขั้นตอนการขาย:' => '销售阶段：'],
        ][$locale];
        foreach ($prefixes as $thai => $translated) {
            if (str_starts_with($text, $thai)) {
                return $translated.' '.ltrim(substr($text, strlen($thai)));
            }
        }

        return $text;
    }

    private function date(string $date): string
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, 'Asia/Bangkok')->format('d/m/Y');
    }
}
