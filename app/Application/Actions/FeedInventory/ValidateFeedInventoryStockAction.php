<?php

declare(strict_types=1);

namespace App\Application\Actions\FeedInventory;

use App\Domain\Exceptions\InsufficientStockException;
use App\Domain\Models\FeedInventory;

final readonly class ValidateFeedInventoryStockAction
{
    /**
     * Garante que há estoque suficiente de ração para a quantidade solicitada.
     *
     * @throws InsufficientStockException
     */
    public function execute(FeedInventory $feedInventory, float $quantity): void
    {
        if ($feedInventory->current_stock < $quantity) {
            throw new InsufficientStockException($quantity, $feedInventory->current_stock);
        }
    }
}
