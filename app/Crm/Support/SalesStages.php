<?php

namespace App\Crm\Support;

final class SalesStages
{
    private const PROBABILITIES = [
        'NEW' => 10, 'NEGOTIATING' => 30, 'REVIEW' => 45, 'MOU' => 65,
        'CONTRACT' => 82, 'SIGNED' => 100, 'ON_HOLD' => 15, 'CANCELLED' => 0,
    ];

    public static function probability(string $stage): ?int
    {
        return self::PROBABILITIES[$stage] ?? null;
    }

    public static function legacyVersion(int $batchId, string $payloadHash): string
    {
        return "legacy:{$batchId}:{$payloadHash}";
    }

    public static function currentVersion(int $version): string
    {
        return "current:{$version}";
    }
}
