<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;

class BatcheDTO
{
    /**
     * @param array{id?: string|null, name?: string|null}|null $tank
     */
    public function __construct(
        public string $id,
        public int $initialQuantity,
        public string $species,
        public Status $status,
        public Cultivation $cultivation,
        public ?array $tank = null,
        public ?string $entryDate = null,
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
            entryDate: $data['entry_date'],
            initialQuantity: (int) $data['initial_quantity'],
            species: $data['species'],
            status: Status::from($data['status']),
            cultivation: Cultivation::from($data['cultivation']),
            tank: isset($data['tank']) ? [
                'id'   => $data['tank']['id'] ?? null,
                'name' => $data['tank']['name'] ?? null,
            ] : null,
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
            'id'              => $this->id,
            'entryDate'       => $this->entryDate,
            'initialQuantity' => $this->initialQuantity,
            'species'         => $this->species,
            'status'          => $this->status->value,
            'cultivation'     => $this->cultivation->value,
            'tank'            => $this->tank,
            'createdAt'      => $this->createdAt,
            'updatedAt'      => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->entryDate === '' || $this->entryDate === '0') &&
               $this->initialQuantity === 0 &&
               ($this->species === '' || $this->species === '0');
    }
}
