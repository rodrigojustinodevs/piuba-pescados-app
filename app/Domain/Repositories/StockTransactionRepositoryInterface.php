<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Models\StockTransaction;

interface StockTransactionRepositoryInterface
{
    /**
     * Create a new stock transaction record.
     */
    public function create(StockTransactionDTO $dto): StockTransaction;

    /**
     * Find a stock transaction by a specific field.
     */
    public function findBy(string $field, string | int $value): ?StockTransaction;

    /**
     * Paginate stock transactions.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters): PaginationInterface;
}
