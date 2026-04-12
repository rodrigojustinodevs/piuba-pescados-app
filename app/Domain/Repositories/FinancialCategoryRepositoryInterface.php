<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\FinancialCategoryInputDTO;
use App\Domain\Models\FinancialCategory;

interface FinancialCategoryRepositoryInterface
{
    /**
     * Create a new financial category record.
     */
    public function create(FinancialCategoryInputDTO $dto): FinancialCategory;

    /**
     * Update an existing financial category record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialCategory;

    /**
     * Delete a financial category record.
     * Throws an exception if the category has linked transactions.
     */
    public function delete(string $id): bool;

    /**
     * Find a category by ID or throw ModelNotFoundException.
     */
    public function findOrFail(string $id): FinancialCategory;

    /**
     * Paginate financial categories filtered by company.
     *
     * @param array{
     *     companyId: string,
     *     type?: string|null,
     *     status?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a financial category by a specific field.
     */
    public function showFinancialCategory(string $field, string | int $value): ?FinancialCategory;

    /**
     * Check whether the category has any linked financial transactions.
     */
    public function hasTransactions(string $id): bool;
}
