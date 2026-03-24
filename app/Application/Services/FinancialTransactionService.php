<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\CategoryTypeMismatchException;
use App\Domain\Exceptions\TransactionAmountImmutableException;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class FinancialTransactionService
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Validates that the transaction type matches the category type.
     * Used on both Create and Update flows.
     *
     * @throws CategoryTypeMismatchException
     */
    public function validateCategoryType(string $categoryId, FinancialType $transactionType): void
    {
        $category = $this->categoryRepository->findOrFail($categoryId);

        if ($category->type !== $transactionType) {
            throw new CategoryTypeMismatchException(
                transactionType: $transactionType,
                categoryType:    $category->type,
            );
        }
    }

    /**
     * Blocks manual amount change when the transaction was originated by an
     * external module (Sale, Purchase, etc.).
     * Used on Update flows (and future external integrations).
     *
     * @throws TransactionAmountImmutableException
     */
    public function guardAmountImmutability(FinancialTransaction $transaction, ?float $newAmount): void
    {
        if (! $transaction->isOriginatedExternally()) {
            return;
        }

        if ($newAmount !== null && $newAmount !== (float) $transaction->amount) {
            throw new TransactionAmountImmutableException($transaction->id);
        }
    }

    /**
     * Enforces the payment_date business rules:
     * - Only set when status = paid; returns null otherwise (clears cash-basis date).
     * - payment_date cannot be a future date.
     *
     * @throws InvalidArgumentException when payment_date is in the future
     */
    public function resolvePaymentDate(
        FinancialTransactionStatus $status,
        ?string $paymentDate,
    ): ?string {
        if (! $status->isPaid()) {
            return null;
        }

        $resolved = $paymentDate ?? CarbonImmutable::today()->toDateString();

        if (CarbonImmutable::parse($resolved)->isAfter(CarbonImmutable::today())) {
            throw new InvalidArgumentException(
                'The payment date cannot be a future date.'
            );
        }

        return $resolved;
    }

    /**
     * Returns a new DTO with payment_date already resolved,
     * so the Create UseCase only needs to persist it.
     */
    public function applyPaymentDateToDTO(FinancialTransactionInputDTO $dto): FinancialTransactionInputDTO
    {
        $resolved = $this->resolvePaymentDate($dto->status, $dto->paymentDate);

        return new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                $dto->type,
            amount:              $dto->amount,
            dueDate:             $dto->dueDate,
            status:              $dto->status,
            paymentDate:         $resolved,
            description:         $dto->description,
            notes:               $dto->notes,
            referenceType:       $dto->referenceType,
            referenceId:         $dto->referenceId,
        );
    }
}
