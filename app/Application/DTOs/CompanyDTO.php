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
            id: (string) $data['id'],  // Garantir que seja string
            name: (string) $data['name'],
            cnpj: (string) $data['cnpj'],
            address: (string) $data['address'],
            phone: (string) $data['phone'],
            status: Status::from((string) $data['status']), // Converter para string antes de passar ao Enum
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null
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

    /**
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->id) && empty($this->name) && empty($this->cnpj) && empty($this->address) && empty($this->phone);
    }
}
