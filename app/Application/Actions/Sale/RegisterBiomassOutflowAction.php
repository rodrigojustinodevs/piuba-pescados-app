<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Application\Actions\Stock\RegisterStockTransactionAction;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final class RegisterBiomassOutflowAction
{
    public function __construct(
        private readonly RegisterStockTransactionAction  $registerStockTransaction,
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly SaleRepositoryInterface         $saleRepository,
    ) {
    }

    /**
     * Regra 3 - CMV exato por stocking_id.
     * O unit_price e calculado pelo custo financeiro acumulado do POVOAMENTO.
     *
     * Formula: custo_total_acumulado_do_stocking / biomassa_restante
     *
     * @param float $alreadySoldWeight Peso ja vendido ANTES desta venda
     */
    public function execute(
        Stocking $stocking,
        Sale     $sale,
        float    $alreadySoldWeight,
    ): StockTransaction {
        $unitCost  = $this->calculateUnitCost($stocking, $alreadySoldWeight);
        $totalCost = round((float) $sale->total_weight * $unitCost, 4);

        return $this->registerStockTransaction->execute(new StockTransactionDTO(
            companyId:     (string) $sale->company_id,
            quantity:      (float)  $sale->total_weight,
            unitPrice:     $unitCost,
            totalCost:     $totalCost,
            unit:          Unit::KG,
            direction:     StockTransactionDirection::OUT,
            referenceId:   (string) $sale->id,
            referenceType: StockTransactionReferenceType::SALE,
        ));
    }

    /**
     * Custo unitario exato (R$/kg) - Regra 3.
     *
     * Custo total acumulado = soma de todas as entradas financeiras do stocking_id
     * Biomassa restante = (current_quantity * average_weight) - peso_ja_vendido
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
