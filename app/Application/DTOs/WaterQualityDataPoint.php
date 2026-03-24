<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class WaterQualityDataPoint
{
    public function __construct(
        public string $timestamp,
        public float $value,
        public float $avg,
        public float $min,
        public float $max,
    ) {
    }
}
