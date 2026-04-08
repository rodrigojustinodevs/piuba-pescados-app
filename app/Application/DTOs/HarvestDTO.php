<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * DTO de resposta do módulo de colheita (Harvest).
 */
final readonly class HarvestDTO
{
    public function __construct(
        public string $id,
        public string $batchId,
        public string $harvestDate,
        public float $totalWeight,
        public float $pricePerKg,
        public float $totalRevenue,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->id === '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'batchId'      => $this->batchId,
            'harvestDate'  => $this->harvestDate,
            'totalWeight'  => $this->totalWeight,
            'pricePerKg'   => $this->pricePerKg,
            'totalRevenue' => $this->totalRevenue,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }
}
