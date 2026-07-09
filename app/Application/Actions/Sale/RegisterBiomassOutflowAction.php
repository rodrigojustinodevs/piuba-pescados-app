<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Application\Actions\Stock\RegisterStockTransactionAction;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;
use App\Domain\Models\Sale;
use App\Domain\Models\SaleItem;
use App\Domain\Models\Stocking;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\SaleItemRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final readonly class RegisterBiomassOutflowAction
{
    public function __construct(
        private RegisterStockTransactionAction $registerStockTransaction,
        private StockingRepositoryInterface $stockingRepository,
        private SaleItemRepositoryInterface $saleItemRepository,
    ) {
    }

    /**
     * Regra 3 — CMV exato por stocking_id, calculado por item.
     *
     * Formula: custo_total_acumulado_do_stocking / biomassa_restante
     *
     * Atualiza o sale_item com os custos calculados (snapshot imutável).
     * Gera um StockTransaction referenciando o sale_item.
     *
     * @param float $alreadySoldWeight Peso já vendido ANTES deste item (deste stocking)
     */
    public function execute(
        Stocking $stocking,
        Sale $sale,
        SaleItem $saleItem,
        float $alreadySoldWeight,
    ): StockTransaction {
        $unitCost  = $this->calculateUnitCost($stocking, $alreadySoldWeight);
        $totalCost = round((float) $saleItem->total_weight * $unitCost, 4);

        $this->saleItemRepository->updateCosts((string) $saleItem->id, $unitCost, $totalCost);

        return $this->registerStockTransaction->execute(new StockTransactionDTO(
            companyId:     (string) $sale->company_id,
            quantity:      (float)  $saleItem->total_weight,
            unitPrice:     $unitCost,
            totalCost:     $totalCost,
            unit:          Unit::KG,
            direction:     StockTransactionDirection::OUT,
            referenceId:   (string) $saleItem->id,
            referenceType: StockTransactionReferenceType::SALE_ITEM,
        ));
    }

    /**
     * Custo unitário exato (R$/kg) — Regra 3.
     *
     * custo total acumulado = soma de todas as entradas financeiras do stocking_id
     * biomassa restante = (current_quantity × average_weight) − peso_já_vendido
     */
    private function calculateUnitCost(Stocking $stocking, float $alreadySoldWeight): float
    {
        $totalAccumulatedCost = $this->stockingRepository
            ->totalAccumulatedCost((string) $stocking->id);

        if ($totalAccumulatedCost <= 0) {
            return 0.0;
        }

        $currentBiomass   = (float) $stocking->current_quantity * (float) $stocking->average_weight;
        $remainingBiomass = $currentBiomass - $alreadySoldWeight;

        if ($remainingBiomass <= 0) {
            return 0.0;
        }

        return round($totalAccumulatedCost / $remainingBiomass, 6);
    }
}
