<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Models\StockBalance;
use App\Domain\Repositories\StockBalanceRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Collection;

class GetStockBalancesUseCase
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository,
        private readonly StockBalanceRepositoryInterface $balanceRepository,
    ) {
    }

    /** @return Collection<int, StockBalance> */
    public function execute(string $stockId): Collection
    {
        $this->stockRepository->findOrFail($stockId);

        return $this->balanceRepository->getByStock($stockId);
    }
}
