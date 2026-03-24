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

final readonly class RegisterBiomassOutflowAction
{
    public function __construct(
        private RegisterStockTransactionAction $registerStockTransaction,
    ) {
    }

    /**
     * Registra a saída de biomassa no livro-razão (stock_transactions).
     *
     * O unit_price é o custo unitário acumulado do lote, calculado com base
     * no peso já vendido anteriormente — isso garante o custo correto por kg
     * para cálculo de lucro por tanque.
     *
     * @param float $alreadySoldWeight Peso vendido ANTES desta venda (exclui a atual)
     */
    public function execute(
        Stocking $stocking,
        Sale $sale,
        float $alreadySoldWeight,
    ): StockTransaction {
        $unitCost  = $stocking->calculateCurrentUnitCost($alreadySoldWeight);
        $totalCost = round((float) $sale->total_weight * $unitCost, 2);

        return $this->registerStockTransaction->execute(new StockTransactionDTO(
            companyId:     (string) $sale->company_id,
            quantity:      (float)  $sale->total_weight,
            unitPrice:     $unitCost,
            totalCost:     $totalCost,
            unit:          Unit::KG,
            direction:     StockTransactionDirection::OUT,
            supplyId:      null,
            referenceId:   (string) $sale->id,
            referenceType: StockTransactionReferenceType::SALE,
        ));
    }
}
