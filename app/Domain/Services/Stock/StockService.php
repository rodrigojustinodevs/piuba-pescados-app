<?php

declare(strict_types=1);

namespace App\Domain\Services\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use RuntimeException;

class StockService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository
    ) {}

    public function findById(string $id): ?Stock
    {
        return $this->stockRepository->showStock('id', $id);
    }

    /**
     * Atualiza um estoque existente. Validações de entrada ficam a cargo da Request.
     */
    public function updateStock(Stock $stock, StockDTO $dto): Stock
    {
        $supplierId = $dto->supplier['id'] ?? null;
        $supplierId = $supplierId !== null && $supplierId !== '' ? $supplierId : null;

        $data = [
            'current_quantity'   => $dto->currentQuantity,
            'unit'               => $dto->unit,
            'unit_price'         => round($dto->unitPrice, 2),
            'minimum_stock'      => $dto->minimumStock,
            'withdrawal_quantity'=> $dto->withdrawalQuantity,
        ];

        if ($supplierId !== null && $supplierId !== '') {
            $data['supplier_id'] = $supplierId;
        } else {
            $data['supplier_id'] = null;
        }

        $updated = $this->stockRepository->update($stock->id, $data);

        if (! $updated instanceof Stock) {
            throw new RuntimeException('Stock not found');
        }

        return $updated;
    }

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

    public function removeStockById(string $stockId, float $amount): void
    {
        $stock = $this->stockRepository->showStock('id', $stockId);

        if (! $stock instanceof Stock || $stock->current_quantity < $amount) {
            throw new RuntimeException('Insufficient stock or stock not found.');
        }

        $this->stockRepository->decrementStock($stockId, $amount);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

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
            'unit_price' => round($newPrice, 2),
        ];

        if ($supplierId !== null) {
            $data['supplier_id'] = $supplierId;
        }

        return $this->stockRepository->update(
            $item->id,
            $data
        );
    }

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
            'company_id' => $companyId,
            'current_quantity' => $quantity,
            'unit_price' => round($entryCost / $quantity, 2),
            'unit' => $unit,
            'minimum_stock' => $minimumStock,
            'withdrawal_quantity' => $withdrawalQuantity,
        ];

        if ($supplierId !== null) {
            $data['supplier_id'] = $supplierId;
        }

        return $this->stockRepository->create($data);
    }
}