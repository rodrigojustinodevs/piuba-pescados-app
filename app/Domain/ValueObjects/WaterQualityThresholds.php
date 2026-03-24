<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Technical thresholds for water quality for aquaculture.
 * Centralizing here avoids the magic values from being scattered
 * in queries, use cases and resources.
 */
final class WaterQualityThresholds
{
    // Ideal pH: 6.5 – 8.5
    public const float PH_MIN = 6.5;
    public const float PH_MAX = 8.5;

    // Dissolved oxygen: critical below 5 mg/L
    public const float DISSOLVED_OXYGEN_MIN = 5.0;

    // Ammonia: toxic above 0.1 mg/L
    public const float AMMONIA_MAX = 0.1;

    // Ideal temperature for tropical fish: 20°C – 32°C
    public const float TEMPERATURE_MIN = 20.0;
    public const float TEMPERATURE_MAX = 32.0;

    public static function isPHCritical(?float $ph): bool
    {
        return $ph !== null && ($ph < self::PH_MIN || $ph > self::PH_MAX);
    }

    public static function isOxygenLow(?float $do): bool
    {
        return $do !== null && $do < self::DISSOLVED_OXYGEN_MIN;
    }

    public static function isAmmoniaCritical(?float $ammonia): bool
    {
        return $ammonia !== null && $ammonia > self::AMMONIA_MAX;
    }

    public static function isTemperatureCritical(?float $temp): bool
    {
        return $temp !== null && ($temp < self::TEMPERATURE_MIN || $temp > self::TEMPERATURE_MAX);
    }

    /**
     * Returns a list of parameters in alert for a quality record.
     *
     * @return string[]
     */
    public static function evaluate(
        ?float $ph,
        ?float $dissolvedOxygen,
        ?float $ammonia,
        ?float $temperature,
    ): array {
        $alerts = [];

        if (self::isPHCritical($ph)) {
            $alerts[] = 'ph';
        }

        if (self::isOxygenLow($dissolvedOxygen)) {
            $alerts[] = 'dissolved_oxygen';
        }

        if (self::isAmmoniaCritical($ammonia)) {
            $alerts[] = 'ammonia';
        }

        if (self::isTemperatureCritical($temperature)) {
            $alerts[] = 'temperature';
        }

        return $alerts;
    }
}
