<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;

final readonly class FinancialTransactionInputDTO
{
    public function __construct(
        public string $companyId,
        public string $financialCategoryId,
        public FinancialType $type,
        public float $amount,
        public string $dueDate,
        public FinancialTransactionStatus $status = FinancialTransactionStatus::PENDING,
        public ?string $paymentDate = null,
        public ?string $description = null,
        public ?string $notes = null,
        public ?FinancialTransactionReferenceType $referenceType = null,
        public ?string $referenceId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $referenceType = isset($data['reference_type'])
            ? FinancialTransactionReferenceType::from((string) $data['reference_type'])
            : null;

        $status = isset($data['status'])
            ? FinancialTransactionStatus::from((string) $data['status'])
            : FinancialTransactionStatus::PENDING;

        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            financialCategoryId: (string) ($data['financial_category_id'] ?? $data['financialCategoryId'] ?? ''),
            type:                FinancialType::from((string) ($data['type'] ?? '')),
            amount:              (float) ($data['amount'] ?? 0),
            dueDate:             (string) ($data['due_date'] ?? $data['dueDate'] ?? ''),
            status:              $status,
            paymentDate:         isset($data['payment_date']) ? (string) $data['payment_date']
                               : (isset($data['paymentDate']) ? (string) $data['paymentDate'] : null),
            description:         isset($data['description']) ? (string) $data['description'] : null,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            referenceType:       $referenceType,
            referenceId:         isset($data['reference_id']) ? (string) $data['reference_id']
                               : (isset($data['referenceId']) ? (string) $data['referenceId'] : null),
        );
    }
}
