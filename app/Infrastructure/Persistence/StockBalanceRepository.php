<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Exceptions\StockMovementException;
use App\Domain\Models\StockBalance;
use App\Domain\Repositories\StockBalanceRepositoryInterface;
use Illuminate\Support\Collection;

class StockBalanceRepository implements StockBalanceRepositoryInterface
{
    /** @return Collection<int, StockBalance> */
    public function getByStock(string $stockId): Collection
    {
        return StockBalance::with(['supply:id,name,unit'])
            ->where('stock_id', $stockId)
            ->get();
    }

    public function findOrCreate(string $stockId, string $supplyId): StockBalance
    {
        return StockBalance::firstOrCreate(
            ['stock_id' => $stockId, 'supply_id' => $supplyId],
            ['quantity' => 0],
        );
    }

    public function incrementQuantity(string $stockId, string $supplyId, float $quantity): StockBalance
    {
        $balance = $this->findOrCreate($stockId, $supplyId);
        $balance->increment('quantity', $quantity);

        return $balance->refresh();
    }

    public function decrementQuantity(string $stockId, string $supplyId, float $quantity): StockBalance
    {
        $balance = StockBalance::where('stock_id', $stockId)
            ->where('supply_id', $supplyId)
            ->lockForUpdate()
            ->first();

        $current = $balance ? (float) $balance->quantity : 0.0;

        if ($current < $quantity) {
            throw StockMovementException::insufficientBalance($quantity, $current);
        }

        if ($balance === null) {
            $balance = $this->findOrCreate($stockId, $supplyId);
        }

        $balance->decrement('quantity', $quantity);

        return $balance->refresh();
    }

    public function setQuantity(string $stockId, string $supplyId, float $quantity): StockBalance
    {
        $balance = $this->findOrCreate($stockId, $supplyId);
        $balance->update(['quantity' => $quantity]);

        return $balance->refresh();
    }
}
