<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;

class TankDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public int $capacityLiters,
        public string $location,
        public Status $status,
        public Cultivation $cultivation,
        public ?array $tankType = null,
        public ?array $company = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * Cria um DTO a partir de um array de dados.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            capacityLiters: (int) $data['capacity_liters'],
            location: $data['location'],
            status: Status::from($data['status']),
            cultivation: Cultivation::from($data['cultivation']),
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
     * Converte o DTO para um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'capacity_liters' => $this->capacityLiters,
            'location'        => $this->location,
            'status'          => $this->status->value,
            'cultivation'     => $this->cultivation->value,
            'tank_type'       => $this->tankType,
            'company'       => $this->company,
            'created_at'      => $this->createdAt,
            'updated_at'      => $this->updatedAt,
        ];
    }

    /**
     * Verifica se o DTO está vazio.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->id) &&
               empty($this->name) &&
               empty($this->capacityLiters) &&
               empty($this->location);
    }
}
