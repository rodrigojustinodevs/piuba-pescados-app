<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\FinancialTransaction;

interface FinancialTransactionRepositoryInterface
{
    /**
     * Create a new financial transaction record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FinancialTransaction;

    /**
     * Update an existing financial transaction record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FinancialTransaction;

    /**
     * Delete a financial transaction record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate financial transaction records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a financial transaction by a specific field.
     */
    public function showFinancialTransaction(string $field, string | int $value): ?FinancialTransaction;
}
