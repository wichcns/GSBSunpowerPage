<?php

namespace App\Crm\Support;

final class EngineeringStages
{
    public const LABELS = [
        'NOT_STARTED' => 'ยังไม่เริ่ม',
        'AWAITING_SURVEY' => 'รอสำรวจ',
        'SURVEYED' => 'สำรวจแล้ว',
        'CONSTRUCTION' => 'กำลังก่อสร้าง',
        'LIVE' => 'เปิดบริการ',
    ];

    public static function allowed(): array
    {
        return array_keys(self::LABELS);
    }

    public static function label(string $stage): string
    {
        return self::LABELS[$stage] ?? $stage;
    }
}
