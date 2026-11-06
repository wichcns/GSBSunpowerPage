<?php

namespace App\Crm\Support;

final class SalesStageGroups
{
    private const GROUPS = [
        'NEW' => 'NEW',
        'NEGOTIATING' => 'NEGOTIATING',
        'REVIEW' => 'REVIEW',
        'INTERNAL_REVIEW' => 'REVIEW',
        'INTERNAL_APPROVED' => 'REVIEW',
        'MOU' => 'MOU',
        'MOU_SIGNING' => 'MOU',
        'MOU_SIGNED' => 'MOU',
        'CONTRACT' => 'CONTRACT',
        'CONTRACT_SIGNING' => 'CONTRACT',
        'SIGNED' => 'SIGNED',
        'CONTRACT_SIGNED' => 'SIGNED',
        'ON_HOLD' => 'ON_HOLD',
        'CANCELLED' => 'CANCELLED',
    ];

    public static function codes(): array
    {
        return ['NEW', 'NEGOTIATING', 'REVIEW', 'MOU', 'CONTRACT', 'SIGNED', 'ON_HOLD', 'CANCELLED'];
    }

    public static function forStage(?string $stage): string
    {
        return self::GROUPS[$stage ?? ''] ?? 'OTHER';
    }

    public static function label(?string $stage): string
    {
        return __('sales_stage_groups.'.self::forStage($stage));
    }
}
