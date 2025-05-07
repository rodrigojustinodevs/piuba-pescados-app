<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class FeedingDTO
{
    public function __construct(
        public string $id,
        public string $batcheId,
        public string $feedingDate,
        public float $quantityProvided,
        public string $feedType,
        public float $stockReductionQuantity,
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
            feedingDate: $data['feeding_date'],
            quantityProvided: (float) $data['quantity_provided'],
            feedType: $data['feed_type'],
            stockReductionQuantity: (float) $data['stock_reduction_quantity'],
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
            'id'                     => $this->id,
            'batcheId'               => $this->batcheId,
            'feedingDate'            => $this->feedingDate,
            'quantityProvided'       => $this->quantityProvided,
            'feedType'               => $this->feedType,
            'stockReductionQuantity' => $this->stockReductionQuantity,
            'createdAt'              => $this->createdAt,
            'updatedAt'              => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->batcheId === '' || $this->batcheId === '0') &&
               ($this->feedingDate === '' || $this->feedingDate === '0') &&
               $this->quantityProvided === 0.0 &&
               ($this->feedType === '' || $this->feedType === '0') &&
               $this->stockReductionQuantity === 0.0;
    }
}
