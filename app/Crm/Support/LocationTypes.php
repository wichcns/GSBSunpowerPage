<?php

namespace App\Crm\Support;

class LocationTypes
{
    public const CODES = ['PETROL_STATION', 'BUSINESS_DISTRICT', 'MALL', 'OPEN_LAND', 'OTHER'];

    private const LEGACY_CODES = [
        'Gas station' => 'PETROL_STATION',
        'Mall' => 'MALL',
        'Empty land' => 'OPEN_LAND',
        'Other' => 'OTHER',
    ];

    public static function codes(): array
    {
        return self::CODES;
    }

    public static function label(?string $code): string
    {
        if ($code === null || $code === '') {
            return __('sites.unspecified_type');
        }

        $translatedCode = self::LEGACY_CODES[$code] ?? $code;
        if (in_array($translatedCode, self::CODES, true)) {
            return __('location_types.'.$translatedCode);
        }

        return __('location_types.legacy.'.$code) === 'location_types.legacy.'.$code
            ? $code : __('location_types.legacy.'.$code);
    }

    public static function legacyLabels(): array
    {
        $labels = [];
        foreach (['Gas station', 'Community mall', 'Market', 'parking lot', 'Mall', 'Other', 'Empty land', 'LPG'] as $value) {
            $labels[$value] = self::label($value);
        }

        return $labels;
    }
}
