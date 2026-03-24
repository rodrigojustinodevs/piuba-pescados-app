<?php

declare(strict_types=1);

namespace App\Application\Actions\Purchase;

use App\Application\DTOs\StockInputDTO;
use App\Application\UseCases\Stock\AddStockEntryBySupplyUseCase;
use App\Domain\Models\Purchase;

final readonly class ApplyPurchaseToStockAction
{
    public function __construct(
        private AddStockEntryBySupplyUseCase $addStockEntry,
    ) {
    }

    public function execute(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            $this->addStockEntry->execute(new StockInputDTO(
                companyId:          (string) $purchase->company_id,
                supplyId:           (string) $item->supply_id,
                quantity:           (float)  $item->quantity,
                unit:               (string) $item->unit,
                unitPrice:          (float)  $item->unit_price,
                totalCost:          (float)  $item->total_price,
                minimumStock:       0,
                withdrawalQuantity: 0,
                referenceId:        (string) $purchase->id,
            ));
        }
    }
}
