<?php

declare(strict_types=1);

namespace App\Domain\Services\Feeding;

use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;

class FeedingService
{
    public function __construct(
        private readonly FeedInventoryRepositoryInterface $feedInventoryRepository,
        private readonly FeedingRepositoryInterface $feedingRepository,
        private readonly StockRepositoryInterface $stockRepository,
    ) {
    }

    /**
     * @return array{stock_reduction_quantity: float, quantity_provided: float}
     */
    public function calculateStockAfterFeedingOperations(
        Feeding $feeding,
        float $quantity
    ): array {
        return [
            'stock_reduction_quantity' => $feeding->stock_reduction_quantity - $quantity,
            'quantity_provided'        => $feeding->quantity_provided + $quantity,
        ];
    }

    /**
     * @param array<string, mixed> $mappedData
     */
    public function calculateStockDelta(Feeding $feeding, array $mappedData): float
    {
        $oldStockReductionQuantity = (float) $feeding->stock_reduction_quantity;
        $newStockReductionQuantity = (float) ($mappedData['stock_reduction_quantity'] ?? $oldStockReductionQuantity);

        return round($newStockReductionQuantity - $oldStockReductionQuantity, 2);
    }

    /**
         * Aplica o efeito de uma nova alimentação ou ajuste no estoque.
         */
    public function applyStockEffect(Feeding $feeding, string $companyId): void
    {
        $inventory = $this->feedInventoryRepository->findByCompanyAndFeedType($companyId, $feeding->feed_type);

        if ($inventory instanceof \App\Domain\Models\FeedInventory) {
            $newDailyAvg = $this->feedingRepository
                ->getDailyConsumptionAverage($companyId, $feeding->feed_type);

            $inventory->update([
                'current_stock'     => $inventory->current_stock - $feeding->stock_reduction_quantity,
                'total_consumption' => $inventory->total_consumption + $feeding->stock_reduction_quantity,
                'daily_consumption' => $newDailyAvg,
            ]);
        }

        $stock = $this->stockRepository->findByCompanyAndSupplyName($companyId, $feeding->feed_type);

        if ($stock instanceof \App\Domain\Models\Stock) {
            $this->stockRepository->decrementStock($stock->id, (float) $feeding->stock_reduction_quantity);
        }
    }

    /**
     * Reverte o efeito de uma alimentação (estorno).
     */
    public function revertStockEffect(Feeding $feeding, string $companyId): void
    {
        $inventory = $this->feedInventoryRepository->findByCompanyAndFeedType($companyId, $feeding->feed_type);

        if ($inventory instanceof \App\Domain\Models\FeedInventory) {
            $inventory->update([
                'current_stock'     => $inventory->current_stock + $feeding->stock_reduction_quantity,
                'total_consumption' => $inventory->total_consumption - $feeding->stock_reduction_quantity,
            ]);
        }

        $stock = $this->stockRepository->findByCompanyAndSupplyName($companyId, $feeding->feed_type);

        if ($stock instanceof \App\Domain\Models\Stock) {
            $stock->increment('current_quantity', $feeding->stock_reduction_quantity);
            $stock->decrement('withdrawn_quantity', $feeding->stock_reduction_quantity);
        }
    }

    public function calculateDensity(float $totalBiomass, int $capacityLiters): float
    {
        if ($capacityLiters <= 0) {
            return 0;
        }

        return round($totalBiomass / $capacityLiters, 2);
    }

    public function checkDensityAlert(float $currentDensity, float $maxDensity = 30.0): bool
    {
        return $currentDensity > $maxDensity;
    }

    public function getDailyRecommendation(float $averageWeight, int $totalQuantity): float
    {
        $percentageOfBodyWeight = match (true) {
            $averageWeight < 10  => 0.10,  // 10% (Alevinos)
            $averageWeight < 50  => 0.06,  // 6%
            $averageWeight < 150 => 0.04,  // 4%
            $averageWeight < 400 => 0.03,  // 3%
            $averageWeight < 800 => 0.02,  // 2%
            default              => 0.015, // 1.5% (Terminação/Abate)
        };

        $totalBiomassKg    = ($averageWeight / 1000) * $totalQuantity;
        $recommendedRation = $totalBiomassKg * $percentageOfBodyWeight;

        return round($recommendedRation, 2);
    }
}
