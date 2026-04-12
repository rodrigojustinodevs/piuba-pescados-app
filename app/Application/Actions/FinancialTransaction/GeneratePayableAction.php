<?php

declare(strict_types=1);

namespace App\Application\Actions\FinancialTransaction;

use App\Application\DTOs\ExpenseInputDTO;
use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\Services\FinancialTransactionService;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;

/**
 * Gera automaticamente o "Contas a Pagar" (PENDING) vinculado à despesa.
 *
 * Não executa se financialCategoryId for null.
 * Chamada dentro de DB::transaction — atomicidade garantida pelo chamador.
 */
final readonly class GeneratePayableAction
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $transactionRepository,
        private FinancialTransactionService $transactionService,
    ) {
    }

    public function execute(ExpenseInputDTO $dto, string $referenceId): void
    {
        if ($dto->financialCategoryId === null) {
            return;
        }

        $this->transactionService->validateCategoryType(
            categoryId:      $dto->financialCategoryId,
            transactionType: FinancialType::EXPENSE,
        );

        $payableDTO = new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                FinancialType::EXPENSE,
            amount:              $dto->totalExpense(),
            dueDate:             $dto->expenseDate,
            status:              FinancialTransactionStatus::PENDING,
            paymentDate:         null,
            description:         "Contas a Pagar — Item de Compra #{$referenceId}",
            notes:               $dto->notes,
            referenceType:       FinancialTransactionReferenceType::PURCHASE_ITEM,
            referenceId:         $referenceId,
        );

        $this->transactionRepository->create(
            $this->transactionService->applyPaymentDateToDTO($payableDTO),
        );
    }
}