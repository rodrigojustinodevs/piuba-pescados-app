<?php

declare(strict_types=1);

namespace App\Application\UseCases\StockTransaction;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;

final readonly class ListStockTransactionsUseCase
{
    public function __construct(
        private StockTransactionRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     direction?: string|null,
     *     referenceType?: string|null,
     *     referenceId?: string|null,
     *     perPage?: int,
     *     page?: int,
     * } $filters
     */
    public function execute(string $referenceId, array $filters = []): PaginationInterface
    {
        $filters['referenceId'] = $referenceId;

        return $this->repository->paginate($filters);
    }
}
