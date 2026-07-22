<?php

declare(strict_types=1);

namespace App\Application\UseCases\StockTransaction;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;

final readonly class ListCompanyStockTransactionsUseCase
{
    public function __construct(
        private StockTransactionRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     direction?: string|null,
     *     reference_type?: string|null,
     *     reference_id?: string|null,
     *     per_page?: int,
     *     page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->repository->paginate($filters);
    }
}
