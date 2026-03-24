<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialCategoryStatus;
use App\Domain\Enums\FinancialType;

final readonly class FinancialCategoryInputDTO
{
    public function __construct(
        public string $companyId,
        public string $name,
        public FinancialType $type,
        public FinancialCategoryStatus $status = FinancialCategoryStatus::ACTIVE,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = FinancialCategoryStatus::ACTIVE;

        if (isset($data['status'])) {
            $status = FinancialCategoryStatus::from((string) $data['status']);
        }

        return new self(
            companyId: (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            name:      (string) ($data['name'] ?? ''),
            type:      FinancialType::from((string) ($data['type'] ?? '')),
            status:    $status,
        );
    }
}
