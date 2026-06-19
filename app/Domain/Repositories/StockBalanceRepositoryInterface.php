<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\StockBalance;
use Illuminate\Support\Collection;

interface StockBalanceRepositoryInterface
{
    /** @return Collection<int, StockBalance> */
    public function getByStock(string $stockId): Collection;

    public function findOrCreate(string $stockId, string $supplyId): StockBalance;

    public function incrementQuantity(string $stockId, string $supplyId, float $quantity): StockBalance;

    public function decrementQuantity(string $stockId, string $supplyId, float $quantity): StockBalance;

    public function setQuantity(string $stockId, string $supplyId, float $quantity): StockBalance;
}
