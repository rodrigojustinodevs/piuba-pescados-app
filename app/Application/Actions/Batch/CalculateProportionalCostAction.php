<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

final readonly class CalculateProportionalCostAction
{
    public function execute(int $quantity, int $totalQuantity, float $totalCost): float
    {
        if ($totalQuantity === 0) {
            return 0.0;
        }

        return ($quantity / $totalQuantity) * $totalCost;
    }
}
