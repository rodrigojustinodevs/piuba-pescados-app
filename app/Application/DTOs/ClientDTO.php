<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class ClientDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $contact = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $personType = null,
        public ?string $documentNumber = null,
        public ?string $address = null,
        public ?array $company = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
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
            contact: $data['contact'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            personType: $data['person_type'] ?? null,
            documentNumber: $data['document_number'] ?? null,
            address: $data['address'] ?? null,
            company: isset($data['company']) ? [
                'name' => $data['company']['name'] ?? null,
            ] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'contact'        => $this->contact,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'personType'     => $this->personType,
            'documentNumber' => $this->documentNumber,
            'address'        => $this->address,
            'company'        => $this->company,
            'createdAt'      => $this->createdAt,
            'updatedAt'      => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') && ($this->name === '' || $this->name === '0');
    }
}
