<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class TankAlertDTO
{
    /**
     * @param string[] $waterQualityAlerts Parâmetros críticos: ph, dissolved_oxygen, ammonia, temperature
     * @param string[] $stockAlerts        Below minimum stock alerts
     * @param string[] $sensorAlerts       Inactive or maintenance sensors
     */
    public function __construct(
        public string $tankId,
        public string $tankName,
        public array $waterQualityAlerts,
        public array $stockAlerts,
        public array $sensorAlerts,
        public ?string $lastMeasuredAt = null,
    ) {
    }

    public function hasAlerts(): bool
    {
        return count($this->waterQualityAlerts) > 0
            || count($this->stockAlerts) > 0
            || count($this->sensorAlerts) > 0;
    }

    public function severity(): string
    {
        // Ammonia and O2 are the most lethal — critical level
        if (
            in_array('ammonia', $this->waterQualityAlerts, strict: true)
            || in_array('dissolved_oxygen', $this->waterQualityAlerts, strict: true)
        ) {
            return 'critical';
        }

        if (count($this->waterQualityAlerts) > 0) {
            return 'warning';
        }

        return 'info';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'tankId'             => $this->tankId,
            'tankName'           => $this->tankName,
            'severity'           => $this->severity(),
            'waterQualityAlerts' => $this->waterQualityAlerts,
            'stockAlerts'        => $this->stockAlerts,
            'sensorAlerts'       => $this->sensorAlerts,
            'lastMeasuredAt'     => $this->lastMeasuredAt,
        ];
    }
}
