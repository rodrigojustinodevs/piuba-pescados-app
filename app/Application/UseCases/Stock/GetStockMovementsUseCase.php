<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockMovementRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;

class GetStockMovementsUseCase
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {
    }

    /** @param array<string, mixed> $filters */
    public function execute(string $stockId, array $filters = []): PaginationInterface
    {
        $this->stockRepository->findOrFail($stockId);

        return $this->movementRepository->paginateByStock($stockId, $filters);
    }
}
