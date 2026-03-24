<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class WaterQualityTrendDTO
{
    /**
     * @param array<int, WaterQualityDataPoint> $dataPoints
     */
    public function __construct(
        public string $tankId,
        public string $tankName,
        public string $parameter,
        public string $unit,
        public array $dataPoints,
        public ?float $currentValue,
        public ?float $minValue,
        public ?float $maxValue,
        public ?float $avgValue,
    ) {
    }
}
