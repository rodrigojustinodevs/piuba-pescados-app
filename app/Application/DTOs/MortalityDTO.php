<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class MortalityDTO
{
    public function __construct(
        public string $id,
        public string $batchId,
        public string $mortalityDate,
        public int $quantity,
        public string $cause,
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
            batchId: $data['batch_id'],
            mortalityDate: $data['mortality_date'] ?? '',
            quantity: (int) $data['quantity'],
            cause: $data['cause'],
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
            'batchId'       => $this->batchId,
            'mortalityDate' => $this->mortalityDate,
            'quantity'      => $this->quantity,
            'cause'         => $this->cause,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->batchId === '' || $this->batchId === '0') &&
               ($this->mortalityDate === '' || $this->mortalityDate === '0') &&
               $this->quantity === 0 &&
               ($this->cause === '' || $this->cause === '0');
    }
}
