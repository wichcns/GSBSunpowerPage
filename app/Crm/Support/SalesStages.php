<?php

namespace App\Crm\Support;

final class SalesStages
{
    private const PROBABILITIES = [
        'NEW' => 10,
        'INTERNAL_REVIEW' => 30,
        'INTERNAL_APPROVED' => 45,
        'MOU_SIGNING' => 65,
        'MOU_SIGNED' => 70,
        'CONTRACT_SIGNING' => 82,
        'CONTRACT_SIGNED' => 100,
        'ON_HOLD' => 15,
        'CANCELLED' => 0,
    ];

    public static function codes(): array
    {
        return array_keys(self::PROBABILITIES);
    }

    public static function probability(string $stage): ?int
    {
        return self::PROBABILITIES[$stage] ?? null;
    }

    public static function createsEngineeringProject(string $stage): bool
    {
        return in_array($stage, ['CONTRACT_SIGNING', 'CONTRACT_SIGNED'], true);
    }

    public static function legacyVersion(int $batchId, string $payloadHash): string
    {
        return "legacy:{$batchId}:{$payloadHash}";
    }

    public static function nativeVersion(int $id): string
    {
        return "native:{$id}";
    }

    public static function currentVersion(int $version): string
    {
        return "current:{$version}";
    }
}
