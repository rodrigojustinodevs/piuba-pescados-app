<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Company;

interface CompanyRepositoryInterface
{
    /**
     * Create a new company record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Company;

    /**
     * Update an existing company record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Company;

    /**
     * Delete a company record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate company records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a company by a specific field.
     */
    public function showCompany(string $field, string | int $value): ?Company;
}
