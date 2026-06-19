<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\WaterQualityLevel;

final class WaterQualityThresholds
{
    public const float PH_MIN               = 6.5;
    public const float PH_MAX               = 8.5;
    public const float DISSOLVED_OXYGEN_MIN = 5.0;
    public const float AMMONIA_MAX          = 0.1;
    public const float TEMPERATURE_MIN      = 20.0;
    public const float TEMPERATURE_MAX      = 32.0;

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
     * Avalia todos os parâmetros e retorna a lista de alertas.
     * Recebe float — sem cast de string dentro de métodos de comparação numérica.
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

    public static function quality(
        ?float $ph,
        ?float $dissolvedOxygen,
        ?float $ammonia,
        ?float $temperature,
    ): WaterQualityLevel {
        if ($ph === null && $dissolvedOxygen === null && $ammonia === null && $temperature === null) {
            return WaterQualityLevel::UNKNOWN;
        }

        $alerts = self::evaluate($ph, $dissolvedOxygen, $ammonia, $temperature);

        if (in_array('ammonia', $alerts, true) || in_array('dissolved_oxygen', $alerts, true)) {
            return WaterQualityLevel::CRITICAL;
        }

        if (count($alerts) >= 2) {
            return WaterQualityLevel::WARNING;
        }

        if (count($alerts) === 1) {
            return WaterQualityLevel::GOOD;
        }

        return WaterQualityLevel::EXCELLENT;
    }
}
