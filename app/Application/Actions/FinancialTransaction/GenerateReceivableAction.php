<?php

declare(strict_types=1);

namespace App\Application\Actions\FinancialTransaction;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\DTOs\SaleInputDTO;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;

/**
 * Gera automaticamente o "Contas a Receber" (PENDING) vinculado à venda.
 *
 * Não executa se financialCategoryId for null.
 * Chamada dentro de DB::transaction — atomicidade garantida pelo chamador.
 */
final readonly class GenerateReceivableAction
{
    public function __construct(
        private GenerateFinancialTransactionAction $generateFinancialTransaction,
    ) {
    }

    public function execute(SaleInputDTO $dto, string $referenceId): void
    {
        $receivableDTO = new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                FinancialType::REVENUE,
            amount:              $dto->totalRevenue(),
            dueDate:             $dto->saleDate,
            status:              FinancialTransactionStatus::PENDING,
            paymentDate:         null,
            description:         "Contas a Receber — Venda #{$referenceId}",
            notes:               $dto->notes,
            referenceType:       FinancialTransactionReferenceType::SALE,
            referenceId:         $referenceId,
        );

        $this->generateFinancialTransaction->execute($receivableDTO);
    }
}
