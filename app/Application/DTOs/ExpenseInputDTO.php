<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialTransactionStatus;

final readonly class ExpenseInputDTO
{
    public function __construct(
        public string $companyId,
        public float $amount,
        public string $expenseDate,
        public ?string $financialCategoryId = null,
        public ?string $supplierId = null,
        public ?string $costCenterId = null,
        public ?string $description = null,
        public ?string $notes = null,
        public FinancialTransactionStatus $status = FinancialTransactionStatus::PENDING,
        public ?string $paymentDate = null,
    ) {
    }

    /**
     * Valor total da despesa — mantém a mesma convenção de totalRevenue() na SaleInputDTO.
     */
    public function totalExpense(): float
    {
        return $this->amount;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = isset($data['status'])
            ? FinancialTransactionStatus::from((string) $data['status'])
            : FinancialTransactionStatus::PENDING;

        return new self(
            companyId: (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            amount: (float) ($data['amount'] ?? 0),
            expenseDate: (string) (
                $data['expense_date'] ?? $data['expenseDate'] ?? ''
            ),
            financialCategoryId: isset($data['financial_category_id'])
                ? (string) $data['financial_category_id']
                : (isset($data['financialCategoryId'])
                    ? (string) $data['financialCategoryId']
                    : null),
            supplierId: isset($data['supplier_id'])
                ? (string) $data['supplier_id']
                : (isset($data['supplierId']) ? (string) $data['supplierId'] : null),
            costCenterId: isset($data['cost_center_id'])
                ? (string) $data['cost_center_id']
                : (isset($data['costCenterId']) ? (string) $data['costCenterId'] : null),
            description: isset($data['description'])
                ? (string) $data['description']
                : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            status: $status,
            paymentDate: isset($data['payment_date'])
                ? (string) $data['payment_date']
                : (isset($data['paymentDate']) ? (string) $data['paymentDate'] : null),
        );
    }
}
