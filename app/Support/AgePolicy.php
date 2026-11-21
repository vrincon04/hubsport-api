<?php

namespace App\Support;

use Carbon\CarbonInterface;

class AgePolicy
{
    public const MIN_AGE = 12;

    public const ADULT_AGE = 18;

    public static function years(?CarbonInterface $birthDate, ?CarbonInterface $now = null): ?int
    {
        if (! $birthDate) {
            return null;
        }

        return $birthDate->diffInYears($now ?? now());
    }

    public static function isUnderMinimum(?CarbonInterface $birthDate): bool
    {
        $years = self::years($birthDate);

        return $years !== null && $years < self::MIN_AGE;
    }

    public static function isMinor(?CarbonInterface $birthDate): bool
    {
        $years = self::years($birthDate);

        return $years !== null && $years >= self::MIN_AGE && $years < self::ADULT_AGE;
    }

    public static function requiresGuardian(?CarbonInterface $birthDate, ?string $profileType): bool
    {
        return self::isMinor($birthDate) && $profileType === 'athlete';
    }
}
