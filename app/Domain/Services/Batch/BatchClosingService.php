<?php

declare(strict_types=1);

namespace App\Domain\Services\Batch;

use App\Domain\Models\Batch;

class BatchClosingService
{
    public function __construct(
        private BatchPerformanceService $performanceService
    ) {}

    /**
     * Calcula o DRE (Demonstrativo de Resultado) direto do lote.
     */
    public function calculateFinalReport(Batch $batch, float $harvestWeight, float $pricePerKg, float $feedPrice): array
    {
        // 1. Receita Bruta (Baseado na tabela harvests)
        $totalRevenue = $harvestWeight * $pricePerKg;

        // 2. Custos Diretos
        $fingerlingCost = $batch->initial_quantity * (float) $batch->unit_cost; // Exige a coluna unit_cost
        $totalFeedCost = $this->performanceService->calculateTotalInvestedInFeed($batch, $feedPrice);
        $totalCosts = $fingerlingCost + $totalFeedCost;

        // 3. Indicadores Financeiros
        $netProfit = $totalRevenue - $totalCosts;
        $costPerKg = $harvestWeight > 0 ? ($totalCosts / $harvestWeight) : 0.0;
        
        // ROI (Retorno sobre Investimento)
        $roi = $totalCosts > 0 ? ($netProfit / $totalCosts) * 100 : 0.0;

        return [
            'gross_revenue' => round($totalRevenue, 2),
            'total_costs' => round($totalCosts, 2),
            'net_profit' => round($netProfit, 2),
            'production_cost_per_kg' => round($costPerKg, 2),
            'roi_percentage' => round($roi, 2),
            'break_even_price' => round($costPerKg, 2) // O preço mínimo que ele poderia vender sem ter prejuízo
        ];
    }
}