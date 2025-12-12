<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\Status;

class CompanyDTO
{
    /**
     * @param array<string, mixed> $address
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $cnpj,
        public ?string $email,
        public string $phone,
        public array $address,
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
        $address = [
            'street'      => $data['address_street'] ?? null,
            'number'      => $data['address_number'] ?? null,
            'complement'  => $data['address_complement'] ?? null,
            'neighborhood' => $data['address_neighborhood'] ?? null,
            'city'        => $data['address_city'] ?? null,
            'state'       => $data['address_state'] ?? null,
            'zipCode'     => $data['address_zip_code'] ?? null,
        ];

        return new self(
            id: $data['id'],
            name: $data['name'],
            cnpj: $data['cnpj'],
            email: $data['email'] ?? null,
            phone: $data['phone'],
            address: $address,
            status: Status::from($data['status'] ?? 'active'),
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
            'id'        => $this->id,
            'name'      => $this->name,
            'cnpj'      => $this->cnpj,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'address'   => $this->address,
            'active'    => $this->status === Status::ACTIVE,
            'status'    => $this->status->value,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->name === '' || $this->name === '0') &&
               ($this->cnpj === '' || $this->cnpj === '0') &&
               ($this->phone === '' || $this->phone === '0');
    }
}
