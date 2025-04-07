<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SupplierDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $contact,
        public string $phone,
        public string $email,
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
            contact: $data['contact'],
            phone: $data['phone'],
            email: $data['email'],
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
            'id'        => $this->id,
            'name'      => $this->name,
            'contact'   => $this->contact,
            'phone'     => $this->phone,
            'email'     => $this->email,
            'company'   => $this->company,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->name === '' || $this->name === '0') &&
               ($this->contact === '' || $this->contact === '0') &&
               ($this->phone === '' || $this->phone === '0') &&
               ($this->email === '' || $this->email === '0');
    }
}
