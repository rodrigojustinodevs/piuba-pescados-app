<?php

declare(strict_types=1);

namespace App\Domain\Services\Stock;

use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use RuntimeException;

class StockService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository
    ) {
    }

    /**
     * Add a new entry to the stock.
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
}
