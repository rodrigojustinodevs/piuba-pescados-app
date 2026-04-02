<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SupplyInputDTO
{
    public function __construct(
        public string $companyId,
        public string $name,
        public ?string $category,
        public string $defaultUnit,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $category = $data['category'] ?? null;

        return new self(
            companyId: (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            category: is_string($category) ? $category : null,
            defaultUnit: (string) ($data['default_unit'] ?? $data['defaultUnit'] ?? ''),
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toPersistence(): array
    {
        return [
            'company_id'    => $this->companyId,
            'name'          => $this->name,
            'category'      => $this->category,
            'default_unit'  => $this->defaultUnit,
        ];
    }
}

