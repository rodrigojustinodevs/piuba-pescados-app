<?php

declare(strict_types=1);

namespace App\Domain\Services\FeedInventoryService;

use App\Domain\Models\FeedInventory;
use Illuminate\Validation\ValidationException;

class FeedInventoryValidatorService
{
    public function validateStock(
        FeedInventory $feedInventory,
        float $quantity
    ): void {
        if ($feedInventory->current_stock < $quantity) {
            throw ValidationException::withMessages([
                'stock_reduction_quantity' => sprintf(
                    'Insufficient stock. Available: %s kg, required: %s kg.',
                    $feedInventory->current_stock,
                    $quantity
                ),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function validateStockChange(FeedInventory $inventory, float $delta): void
    {
        if ($delta > 0 && $inventory->current_stock < $delta) {
            throw ValidationException::withMessages([
                'stock' => 'Insufficient feed inventory.'
            ]);
        }
    }
}
