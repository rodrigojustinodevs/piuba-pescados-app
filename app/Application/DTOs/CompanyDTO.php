<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\Status;

class CompanyDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $cnpj,
        public string $address,
        public string $phone,
        public Status $status,
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
            cnpj: $data['cnpj'],
            address: $data['address'],
            phone: $data['phone'],
            status: Status::from($data['status']),
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
            'id'         => $this->id,
            'name'       => $this->name,
            'cnpj'       => $this->cnpj,
            'address'    => $this->address,
            'phone'      => $this->phone,
            'status'     => $this->status->value,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->name === '' || $this->name === '0') &&
               ($this->cnpj === '' || $this->cnpj === '0') &&
               ($this->address === '' || $this->address === '0') &&
               ($this->phone === '' || $this->phone === '0');
    }
}
