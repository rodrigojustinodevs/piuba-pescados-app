<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final readonly class ListStockingsUseCase
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->stockingRepository->paginate($filters);
    }
}
