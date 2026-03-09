<?php

declare(strict_types=1);

namespace App\Domain\Services\FeedInventoryService;

use App\Domain\Models\FeedInventory;

class FeedInventoryService
{
    /**
     * @return array{current_stock: float, total_consumption: float}
     */
    public function calculateStockAfterFeedingOperations(
        FeedInventory $feedInventory,
        float $quantity
    ): array {
        return [
            'current_stock' => $feedInventory->current_stock - $quantity,
            'total_consumption' => $feedInventory->total_consumption + $quantity,
        ];
    }
}
