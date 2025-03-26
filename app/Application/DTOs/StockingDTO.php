<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class StockingDTO
{
    public function __construct(
        public string $id,
        public string $batcheId,
        public string $stockingDate,
        public int $quantity,
        public float $averageWeight,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            batcheId: $data['batche_id'],
            stockingDate: $data['stocking_date'],
            quantity: (int) $data['quantity'],
            averageWeight: (float) $data['average_weight'],
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
            'id'            => $this->id,
            'batcheId'      => $this->batcheId,
            'stockingDate'  => $this->stockingDate,
            'quantity'      => $this->quantity,
            'averageWeight' => $this->averageWeight,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->batcheId === '' || $this->batcheId === '0') &&
               ($this->stockingDate === '' || $this->stockingDate === '0') &&
               $this->quantity === 0 &&
               $this->averageWeight === 0.0;
    }
}
