<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\AllocationMethod;

final readonly class CostAllocationInputDTO
{
    /**
     * @param string[] $stockingIds
     */
    public function __construct(
        public string $companyId,
        public string $financialTransactionId,
        public AllocationMethod $allocationMethod,
        public array $stockingIds,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:              (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            financialTransactionId: (string) ($data['financial_transaction_id']
                                   ?? $data['financialTransactionId'] ?? ''),
            allocationMethod:       AllocationMethod::from(
                (string) ($data['allocation_method'] ?? $data['allocationMethod'] ?? '')
            ),
            stockingIds:            array_map(
                strval(...),
                array_column((array) ($data['allocations'] ?? []), 'stocking_id')
            ),
            notes:                  isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
