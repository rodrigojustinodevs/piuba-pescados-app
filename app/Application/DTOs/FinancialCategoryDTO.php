<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialCategoryStatus;
use App\Domain\Enums\FinancialType;

final readonly class FinancialCategoryDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $name,
        public FinancialType $type,
        public FinancialCategoryStatus $status = FinancialCategoryStatus::ACTIVE,
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
        $status = FinancialCategoryStatus::ACTIVE;

        if (isset($data['status'])) {
            $status = $data['status'] instanceof FinancialCategoryStatus
                ? $data['status']
                : FinancialCategoryStatus::from((string) $data['status']);
        }

        return new self(
            id:        (string) ($data['id'] ?? ''),
            name:      (string) ($data['name'] ?? ''),
            type:      FinancialType::from((string) ($data['type'] ?? '')),
            status:    $status,
            company:   isset($data['company']) ? ['name' => $data['company']['name'] ?? null] : null,
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }
}
