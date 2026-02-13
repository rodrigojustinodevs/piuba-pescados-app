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
     *
     * @param int         $perPage Number of items per page (limit).
     * @param string|null $search  Optional search term (filters by name, cnpj, email).
     */
    public function paginate(int $perPage = 25, ?string $search = null): PaginationInterface;

    /**
     * Find a company by a specific field.
     */
    public function showCompany(string $field, string | int $value): ?Company;
}
