<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;

final readonly class ListStocksUseCase
{
    public function __construct(
        private StockRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     supply_id?: string|null,
     *     supplier_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->repository->paginate($filters);
    }
}
