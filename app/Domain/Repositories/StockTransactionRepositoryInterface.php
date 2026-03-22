<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\StockTransaction;
use App\Application\DTOs\StockTransactionDTO;

interface StockTransactionRepositoryInterface
{
    /**
     * Create a new stock transaction record.
     *
     * @param array<string, mixed> $data
     */

    public function create(StockTransactionDTO $dto): StockTransaction;

    /**
     * Find a stock transaction by a specific field.
     */
    public function findBy(string $field, string|int $value): ?StockTransaction;

    /**
     * Paginate stock transactions.
     */
    public function paginate(array $filters): PaginationInterface;
}

