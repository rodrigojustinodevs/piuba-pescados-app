<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\DTOs\SaleInputDTO;
use App\Application\UseCases\FinancialTransaction\CreateFinancialTransactionUseCase;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\InsufficientBiomassException;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;

final readonly class SaleService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private FinancialTransactionService $transactionService,
        private CreateFinancialTransactionUseCase $createFinancialTransaction,
    ) {
    }

    /**
     * Validates available biomass for a stocking before creating or updating a sale.
     *
     * Available = stocking.quantity × stocking.average_weight − committed_sold_weight
     *
     * On UPDATE pass the current sale's ID as $excludeSaleId so its own committed
     * weight is not double-counted against the new value being validated.
     *
     * @throws InsufficientBiomassException
     */
    public function guardBiomass(
        string $stockingId,
        float $requestedWeight,
        ?string $excludeSaleId = null,
    ): void {
        $stocking = Stocking::findOrFail($stockingId);

        $initialBiomass  = (float) $stocking->quantity * (float) $stocking->average_weight;
        $committedWeight = $this->saleRepository->soldWeightByStocking($stockingId, $excludeSaleId);
        $available       = $initialBiomass - $committedWeight;

        if ($requestedWeight > $available) {
            throw new InsufficientBiomassException(
                available:  $available,
                requested:  $requestedWeight,
                stockingId: $stockingId,
            );
        }
    }

    /**
     * Auto-generates the "Contas a Receber" (Receivable) linked to the sale.
     *
     * Uses FinancialTransactionService to validate the category type (must be REVENUE)
     * before delegating creation — no duplication of the category-type rule.
     */
    public function generateReceivable(SaleInputDTO $dto, Sale $sale): void
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
            description:         "Contas a Receber — Venda #{$sale->id}",
            referenceType:       FinancialTransactionReferenceType::SALE,
            referenceId:         $sale->id,
        );

        $this->createFinancialTransaction->execute([
            'company_id'            => $receivableDTO->companyId,
            'financial_category_id' => $receivableDTO->financialCategoryId,
            'type'                  => $receivableDTO->type->value,
            'amount'                => $receivableDTO->amount,
            'due_date'              => $receivableDTO->dueDate,
            'status'                => $receivableDTO->status->value,
            'description'           => $receivableDTO->description,
            'reference_type'        => $receivableDTO->referenceType?->value,
            'reference_id'          => $receivableDTO->referenceId,
        ]);
    }
}
