<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SupplierInputDTO
{
    public function __construct(
        public string $companyId,
        public string $name,
        public string $contact,
        public string $phone,
        public string $email,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId: (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            name:      (string) ($data['name'] ?? ''),
            contact:   (string) ($data['contact'] ?? ''),
            phone:     (string) ($data['phone'] ?? ''),
            email:     (string) ($data['email'] ?? ''),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toPersistence(): array
    {
        return [
            'company_id' => $this->companyId,
            'name'       => $this->name,
            'contact'    => $this->contact,
            'phone'      => $this->phone,
            'email'      => $this->email,
        ];
    }
}
