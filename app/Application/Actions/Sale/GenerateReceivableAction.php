<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\DTOs\SaleInputDTO;
use App\Application\Services\FinancialTransactionService;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Models\Sale;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;

/**
 * Gera automaticamente o "Contas a Receber" (PENDING) vinculado à venda.
 *
 * Não executa se financialCategoryId for null.
 * Chamada dentro de DB::transaction — atomicidade garantida pelo chamador.
 */
final readonly class GenerateReceivableAction
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $transactionRepository,
        private FinancialTransactionService $transactionService,
    ) {
    }

    public function execute(SaleInputDTO $dto, Sale $sale): void
    {
        if ($dto->financialCategoryId === null) {
            return;
        }

        $this->transactionService->validateCategoryType(
            categoryId:      $dto->financialCategoryId,
            transactionType: FinancialType::REVENUE,
        );

        $receivableDTO = new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                FinancialType::REVENUE,
            amount:              $dto->totalRevenue(),
            dueDate:             $dto->saleDate,
            status:              FinancialTransactionStatus::PENDING,
            paymentDate:         null,
            description:         "Contas a Receber — Venda #{$sale->id}",
            referenceType:       FinancialTransactionReferenceType::SALE,
            referenceId:         (string) $sale->id,
        );

        $this->transactionRepository->create(
            $this->transactionService->applyPaymentDateToDTO($receivableDTO),
        );
    }
}
