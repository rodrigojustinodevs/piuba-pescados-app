<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SettlementDTO
{
    public function __construct(
        public string $id,
        public string $batcheId,
        public string $settlementDate,
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
            settlementDate: $data['settlement_date'],
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
            'settlementDate'  => $this->settlementDate,
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
               ($this->settlementDate === '' || $this->settlementDate === '0') &&
               $this->quantity === 0 &&
               $this->averageWeight === 0.0;
    }
}
