<?php

namespace App\Crm\Support;

final class LineNotificationEvents
{
    public const LABELS = [
        'site_owner_assigned' => 'มอบหมายผู้รับผิดชอบสถานที่',
        'sales_stage_changed' => 'เปลี่ยนขั้นตอนการขาย',
        'engineering_stage_changed' => 'เปลี่ยนขั้นวิศวกรรม',
        'construction_started' => 'เริ่มงานก่อสร้าง',
        'construction_progress_updated' => 'อัปเดตความคืบหน้างานก่อสร้าง',
        'service_opened' => 'เปิดให้บริการสำเร็จ',
        'task_due_soon' => 'งานใกล้ครบกำหนด',
        'task_overdue' => 'งานเกินกำหนด',
    ];
}
