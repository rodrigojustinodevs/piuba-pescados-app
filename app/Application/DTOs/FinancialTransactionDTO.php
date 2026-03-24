<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;

final readonly class FinancialTransactionDTO
{
    /**
     * @param array{id?: string, name?: string|null, type?: string|null}|null $category
     * @param array{name?: string|null}|null                                  $company
     */
    public function __construct(
        public string $id,
        public FinancialType $type,
        public FinancialTransactionStatus $status,
        public float $amount,
        public string $dueDate,
        public ?string $paymentDate = null,
        public ?string $description = null,
        public ?string $notes = null,
        public ?FinancialTransactionReferenceType $referenceType = null,
        public ?string $referenceId = null,
        public ?array $company = null,
        public ?array $category = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
