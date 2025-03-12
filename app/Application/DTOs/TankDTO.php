<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;

class TankDTO
{
    /**
     * @param array{id?: string|null, name?: string|null}|null $tankType
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $capacityLiters,
        public int $volume,
        public string $location,
        public Status $status,
        public ?array $tankType = null,
        public ?array $company = null,
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
            name: $data['name'],
            capacityLiters: (int) $data['capacity_liters'],
            volume: (int) $data['volume'],
            location: $data['location'],
            status: Status::from($data['status']),
            tankType: isset($data['tank_type']) ? [
                'id'   => $data['tank_type']['id'] ?? null,
                'name' => $data['tank_type']['name'] ?? null,
            ] : null,
            company: isset($data['company']) ? [
                'name' => $data['company']['name'] ?? null,
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
            'name'            => $this->name,
            'capacityLiters' => $this->capacityLiters,
            'volume'          => $this->volume,
            'location'        => $this->location,
            'status'          => $this->status->value,
            'tankType'       => $this->tankType,
            'company'         => $this->company,
            'createdAt'      => $this->createdAt,
            'updatedAt'      => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->name === '' || $this->name === '0') &&
               $this->capacityLiters === 0 &&
               ($this->location === '' || $this->location === '0');
    }
}
