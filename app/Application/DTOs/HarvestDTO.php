<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class HarvestDTO
{
    public function __construct(
        public string $id,
        public string $batcheId,
        public string $harvestDate,
        public float $totalWeight,
        public float $pricePerKg,
        public float $totalRevenue,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            batcheId: $data['batche_id'],
            harvestDate: $data['harvest_date'],
            totalWeight: (float) $data['total_weight'],
            pricePerKg: (float) $data['price_per_kg'],
            totalRevenue: (float) $data['total_revenue'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'batcheId'     => $this->batcheId,
            'harvestDate'  => $this->harvestDate,
            'totalWeight'  => $this->totalWeight,
            'pricePerKg'   => $this->pricePerKg,
            'totalRevenue' => $this->totalRevenue,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
            ($this->batcheId === '' || $this->batcheId === '0') &&
            ($this->harvestDate === '' || $this->harvestDate === '0') &&
            ($this->totalWeight === 0.0) &&
            $this->pricePerKg === 0.0 &&
            $this->totalRevenue === 0.0;
    }
}
