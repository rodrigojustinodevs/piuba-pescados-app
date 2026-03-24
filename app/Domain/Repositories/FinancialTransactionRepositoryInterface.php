<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Domain\Models\FinancialTransaction;

interface FinancialTransactionRepositoryInterface
{
    /**
     * Create a new financial transaction record.
     */
    public function create(FinancialTransactionInputDTO $dto): FinancialTransaction;

    /**
     * Update an existing financial transaction record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialTransaction;

    /**
     * Delete a financial transaction record (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Find a transaction by ID or throw ModelNotFoundException.
     */
    public function findOrFail(string $id): FinancialTransaction;

    /**
     * Paginate financial transactions filtered by company.
     *
     * @param array{
     *     company_id: string,
     *     status?: string|null,
     *     type?: string|null,
     *     financial_category_id?: string|null,
     *     due_date_from?: string|null,
     *     due_date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a financial transaction by a specific field.
     */
    public function showFinancialTransaction(string $field, string | int $value): ?FinancialTransaction;
}
