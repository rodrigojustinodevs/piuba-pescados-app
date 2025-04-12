<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\FinancialCategory;

interface FinancialCategoryRepositoryInterface
{
    /**
     * Create a new financial category record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FinancialCategory;

    /**
     * Update an existing financial category record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FinancialCategory;

    /**
     * Delete a financial category record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate financial category records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a financial category by a specific field.
     */
    public function showFinancialCategory(string $field, string | int $value): ?FinancialCategory;
}
