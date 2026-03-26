<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Application\Services\Batch\BatchPerformanceService;
use App\Domain\Models\Batch;

final readonly class CalculateBatchFinalReportAction
{
    public function __construct(
        private BatchPerformanceService $performanceService,
    ) {
    }

    /**
     * @return array<string, float>
     */
    public function execute(Batch $batch, float $harvestWeight, float $pricePerKg, float $feedPrice): array
    {
        $totalRevenue   = $harvestWeight * $pricePerKg;
        $fingerlingCost = $batch->initial_quantity * (float) $batch->unit_cost;
        $totalFeedCost  = $this->performanceService->calculateTotalInvestedInFeed($batch, $feedPrice);
        $totalCosts     = $fingerlingCost + $totalFeedCost;

        $netProfit = $totalRevenue - $totalCosts;
        $costPerKg = $harvestWeight > 0 ? ($totalCosts / $harvestWeight) : 0.0;
        $roi       = $totalCosts > 0 ? ($netProfit / $totalCosts) * 100 : 0.0;

        return [
            'gross_revenue'          => round($totalRevenue, 2),
            'total_costs'            => round($totalCosts, 2),
            'net_profit'             => round($netProfit, 2),
            'production_cost_per_kg' => round($costPerKg, 2),
            'roi_percentage'         => round($roi, 2),
            'break_even_price'       => round($costPerKg, 2),
        ];
    }
}
