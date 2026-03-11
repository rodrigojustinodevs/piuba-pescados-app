<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SurvivalRateDTO
{
    public function __construct(
        public string $batchId,
        public int $initialQuantity,
        public int $totalMortalities,
        public int $currentSurvivors,
        public float $survivalRate
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'batchId'          => $this->batchId,
            'initialQuantity'  => $this->initialQuantity,
            'totalMortalities' => $this->totalMortalities,
            'currentSurvivors' => $this->currentSurvivors,
            'survivalRate'     => round($this->survivalRate, 2),
        ];
    }
}
