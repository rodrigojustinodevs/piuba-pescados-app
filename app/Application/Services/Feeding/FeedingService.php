<?php

declare(strict_types=1);

namespace App\Application\Services\Feeding;

use App\Domain\Models\Feeding;
use App\Domain\Models\FeedInventory;
use App\Domain\Models\Stock;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;

final readonly class FeedingService
{
    public function __construct(
        private FeedInventoryRepositoryInterface $feedInventoryRepository,
        private FeedingRepositoryInterface $feedingRepository,
        private StockRepositoryInterface $stockRepository,
    ) {
    }

    public function applyStockEffect(Feeding $feeding, string $companyId): void
    {
        $inventory = $this->feedInventoryRepository->findByCompanyAndFeedType($companyId, $feeding->feed_type);

        if ($inventory instanceof FeedInventory) {
            $newDailyAvg = $this->feedingRepository
                ->getDailyConsumptionAverage($companyId, $feeding->feed_type);

            $inventory->update([
                'current_stock'     => $inventory->current_stock - $feeding->stock_reduction_quantity,
                'total_consumption' => $inventory->total_consumption + $feeding->stock_reduction_quantity,
                'daily_consumption' => $newDailyAvg,
            ]);
        }

        if ($feeding->stock_id !== null) {
            $this->stockRepository->decrementQuantity($feeding->stock_id, (float) $feeding->stock_reduction_quantity);
        }
    }

    public function revertStockEffect(Feeding $feeding, string $companyId): void
    {
        $inventory = $this->feedInventoryRepository->findByCompanyAndFeedType($companyId, $feeding->feed_type);

        if ($inventory instanceof FeedInventory) {
            $inventory->update([
                'current_stock'     => $inventory->current_stock + $feeding->stock_reduction_quantity,
                'total_consumption' => $inventory->total_consumption - $feeding->stock_reduction_quantity,
            ]);
        }

        if ($feeding->stock_id !== null) {
            $stock = $this->stockRepository->showStock('id', $feeding->stock_id);

            if ($stock instanceof Stock) {
                $this->stockRepository->incrementQuantity(
                    $feeding->stock_id,
                    (float) $feeding->stock_reduction_quantity,
                );
            }
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
            $averageWeight < 10  => 0.10,
            $averageWeight < 50  => 0.06,
            $averageWeight < 150 => 0.04,
            $averageWeight < 400 => 0.03,
            $averageWeight < 800 => 0.02,
            default              => 0.015,
        };

        $totalBiomassKg    = ($averageWeight / 1000) * $totalQuantity;
        $recommendedRation = $totalBiomassKg * $percentageOfBodyWeight;

        return round($recommendedRation, 2);
    }
}
