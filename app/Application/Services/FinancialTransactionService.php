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
     * @throws CategoryTypeMismatchException
     */
    public function validateCategoryType(
        string $categoryId,
        FinancialType $transactionType,
    ): void {
        $category = $this->categoryRepository->findOrFail($categoryId);

        if ($category->type !== $transactionType) {
            throw new CategoryTypeMismatchException(
                transactionType: $transactionType,
                categoryType:    $category->type,
            );
        }
    }

    /**
     * @throws TransactionAmountImmutableException
     */
    public function guardAmountImmutability(
        FinancialTransaction $transaction,
        ?float $newAmount,
    ): void {
        if (! $transaction->isOriginatedExternally()) {
            return;
        }

        if ($newAmount !== null && $newAmount !== (float) $transaction->amount) {
            throw new TransactionAmountImmutableException($transaction->id);
        }
    }

    /**
     * @throws InvalidArgumentException
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

    public function applyPaymentDateToDTO(FinancialTransactionInputDTO $dto): FinancialTransactionInputDTO
    {
        return new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                $dto->type,
            amount:              $dto->amount,
            dueDate:             $dto->dueDate,
            status:              $dto->status,
            paymentDate:         $this->resolvePaymentDate($dto->status, $dto->paymentDate),
            description:         $dto->description,
            notes:               $dto->notes,
            referenceType:       $dto->referenceType,
            referenceId:         $dto->referenceId,
        );
    }
}
