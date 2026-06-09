<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class DashboardSummaryDTO
{
    public function __construct(
        public int $totalTanks,
        public int $tanksWithAlerts,
        public int $criticalAlerts,
        public int $readingsLast24h,
        public int $stocksBelowMinimum,
        public int $inactiveSensors,
        public float $score,
        public int $excellent,
        public int $good,
        public int $warning,
        public int $critical,
    ) {
    }

    /** @return array<string, int|float> */
    public function toArray(): array
    {
        return [
            'totalTanks'         => $this->totalTanks,
            'tanksWithAlerts'    => $this->tanksWithAlerts,
            'criticalAlerts'     => $this->criticalAlerts,
            'readingsLast24h'    => $this->readingsLast24h,
            'stocksBelowMinimum' => $this->stocksBelowMinimum,
            'inactiveSensors'    => $this->inactiveSensors,
            'score'              => $this->score,
            'excellent'          => $this->excellent,
            'good'               => $this->good,
            'warning'            => $this->warning,
            'critical'           => $this->critical,
        ];
    }
}
