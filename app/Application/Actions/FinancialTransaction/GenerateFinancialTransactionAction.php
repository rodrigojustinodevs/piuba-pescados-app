<?php

declare(strict_types=1);

namespace App\Application\Actions\FinancialTransaction;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\Services\FinancialTransactionService;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;

/**
 * Gera uma transação financeira validando categoria/tipo e regras de pagamento.
 */
final readonly class GenerateFinancialTransactionAction
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $transactionRepository,
        private FinancialTransactionService $transactionService,
    ) {
    }

    public function execute(FinancialTransactionInputDTO $dto): void
    {
        if ($dto->financialCategoryId === '') {
            return;
        }

        $this->transactionService->validateCategoryType(
            categoryId: $dto->financialCategoryId,
            transactionType: $dto->type,
        );

        $this->transactionRepository->create(
            $this->transactionService->applyPaymentDateToDTO($dto),
        );
    }
}
