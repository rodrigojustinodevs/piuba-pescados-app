<?php

declare(strict_types=1);

namespace App\Domain\Services\Stock;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;
use App\Domain\Models\Stock;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class StockService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository,
        private readonly StockTransactionRepositoryInterface $stockTransactionRepository,
    ) {
    }

    /**
     * Add a new entry to the stock by supply (insumo). Usado quando a compra é por supply_id.
     */
    public function addEntryBySupply(
        string $companyId,
        string $supplyId,
        float $quantity,
        float $totalCost,
        float $unitPrice,
        Unit $unit,
        float $minimumStock = 0.0,
        float $withdrawalQuantity = 0.0,
        ?string $supplierId = null,
        ?string $referenceId = null,
        StockTransactionDirection $direction = StockTransactionDirection::IN,
    ): Stock {
        if ($quantity <= 0) {
            throw new RuntimeException('Stock quantity must be greater than zero.');
        }
        $item = $this->stockRepository->findByCompanyAndSupply($companyId, $supplyId);
        $entryCost = $this->resolveEntryCost($quantity, $totalCost, $unitPrice);
        $referenceType = StockTransactionReferenceType::PURCHASE_ITEM;

        if ($item instanceof Stock) {
            $updatedStock = $this->updateExistingStockBySupply($item, $quantity, $entryCost, $supplierId);

            $this->createStockTransaction(
                $companyId,
                $supplyId,
                $referenceType,
                $referenceId,
                $quantity,
                $unitPrice,
                $unit,
                $direction,
            );

            return $updatedStock;
        }
        
        $newStock = $this->createNewStockBySupply(
            $companyId,
            $supplyId,
            $quantity,
            $entryCost,
            $unit,
            $minimumStock,
            $withdrawalQuantity,
            $supplierId
        );

        $this->createStockTransaction(
            $companyId,
            $supplyId,
            $referenceType,
            $referenceId,
            $quantity,
            $unitPrice,
            $unit,
            $direction,
        );

        return $newStock;
    }

    /**
     * Add a new entry to the stock (legado por fornecedor).
     */
    public function addEntry(
        string $companyId,
        float $quantity,
        float $totalCost,
        float $unitPrice,
        string $unit,
        float $minimumStock = 0.0,
        float $withdrawalQuantity = 0.0,
        ?string $supplierId = null
    ): Stock {
        if ($quantity <= 0) {
            throw new RuntimeException('Stock quantity must be greater than zero.');
        }

        $item = null;

        if ($supplierId !== null && $supplierId !== '') {
            $item = $this->stockRepository
                ->findByCompanyAndSupplier($companyId, $supplierId);
        }

        $entryCost = $this->resolveEntryCost($quantity, $totalCost, $unitPrice);

        if ($item instanceof Stock) {
            return $this->updateExistingStock($item, $quantity, $entryCost, $supplierId);
        }

        return $this->createNewStock(
            $companyId,
            $quantity,
            $entryCost,
            $unit,
            $minimumStock,
            $withdrawalQuantity,
            $supplierId
        );
    }

    /**
     * Remove stock by ID.
     */
    public function removeStockById(string $stockId, float $amount): void
    {
        $stock = $this->stockRepository->showStock('id', $stockId);

        if (! $stock instanceof Stock || $stock->current_quantity < $amount) {
            throw new RuntimeException('Insufficient stock or stock not found.');
        }

        $this->stockRepository->decrementStock($stockId, $amount);
    }

    private function resolveEntryCost(
        float $quantity,
        float $totalCost,
        float $unitPrice
    ): float {
        if ($totalCost > 0) {
            return $totalCost;
        }

        return $unitPrice * $quantity;
    }

    /**
     * Update an existing stock.
     */
    private function updateExistingStock(
        Stock $item,
        float $quantity,
        float $entryCost,
        ?string $supplierId = null
    ): Stock {
        $newQuantity = $item->current_quantity + $quantity;

        $newPrice = (
            ($item->current_quantity * $item->unit_price)
            + $entryCost
        ) / $newQuantity;

        $data = [
            'current_quantity' => $newQuantity,
            'unit_price'       => round($newPrice, 2),
        ];

        if ($supplierId !== null) {
            $data['supplier_id'] = $supplierId;
        }

        return $this->stockRepository->update(
            $item->id,
            $data
        );
    }

    /**
     * Create a new stock.
     */
    private function createNewStock(
        string $companyId,
        float $quantity,
        float $entryCost,
        string $unit,
        float $minimumStock = 0.0,
        float $withdrawalQuantity = 0.0,
        ?string $supplierId = null
    ): Stock {
        $data = [
            'company_id'          => $companyId,
            'current_quantity'    => $quantity,
            'unit_price'          => round($entryCost / $quantity, 2),
            'unit'                => $unit,
            'minimum_stock'       => $minimumStock,
            'withdrawal_quantity' => $withdrawalQuantity,
        ];

        if ($supplierId !== null) {
            $data['supplier_id'] = $supplierId;
        }

        return $this->stockRepository->create($data);
    }

    private function updateExistingStockBySupply(
        Stock $item,
        float $quantity,
        float $entryCost,
        ?string $supplierId = null
    ): Stock {
        $newQuantity = $item->current_quantity + $quantity;
        $newPrice = (
            ((float) $item->current_quantity * (float) $item->unit_price)
            + $entryCost
        ) / $newQuantity;

        $data = [
            'current_quantity' => $newQuantity,
            'unit_price'       => round($newPrice, 4),
        ];

        if ($supplierId !== null) {
            $data['supplier_id'] = $supplierId;
        }

        return $this->stockRepository->update($item->id, $data) ?? $item;
    }

    private function createNewStockBySupply(
        string $companyId,
        string $supplyId,
        float $quantity,
        float $entryCost,
        Unit $unit,
        float $minimumStock = 0.0,
        float $withdrawalQuantity = 0.0,
        ?string $supplierId = null
    ): Stock {
        $data = [
            'company_id'          => $companyId,
            'supply_id'           => $supplyId,
            'current_quantity'    => $quantity,
            'unit_price'          => round($entryCost / $quantity, 4),
            'unit'                => $unit->value,
            'minimum_stock'       => $minimumStock,
            'withdrawal_quantity' => $withdrawalQuantity,
        ];

        if ($supplierId !== null) {
            $data['supplier_id'] = $supplierId;
        }

        return $this->stockRepository->create($data);
    }

    private function createStockTransaction(
        string $companyId,
        string $supplyId,
        StockTransactionReferenceType $referenceType,
        string $referenceId,
        float $quantity,
        float $unitPrice,
        Unit $unit = Unit::KG,
        StockTransactionDirection $direction = StockTransactionDirection::IN,
    ): StockTransaction {
        return $this->stockTransactionRepository->create(
            [
            'company_id' => $companyId,
            'supply_id' => $supplyId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'direction' => $direction,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit' => $unit,
            'created_by' => Auth::user()?->id ?? null
            ]
        );
    }
}
