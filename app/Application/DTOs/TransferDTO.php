<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class TransferDTO
{
    public function __construct(
        public string $id,
        public string $batcheId,
        public string $originTankId,
        public string $destinationTankId,
        public int $quantity,
        public string $description,
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
            originTankId: $data['origin_tank_id'],
            destinationTankId: $data['destination_tank_id'],
            quantity: (int) $data['quantity'],
            description: $data['description'],
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
            'id'                => $this->id,
            'batcheId'          => $this->batcheId,
            'originTankId'      => $this->originTankId,
            'destinationTankId' => $this->destinationTankId,
            'quantity'          => $this->quantity,
            'description'       => $this->description,
            'createdAt'         => $this->createdAt,
            'updatedAt'         => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' ||
            $this->batcheId === '' ||
            $this->originTankId === '' ||
            $this->destinationTankId === '' ||
            $this->description === '' ||
            $this->quantity === 0;
    }
}
