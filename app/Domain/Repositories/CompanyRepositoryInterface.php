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
     * @return Company
     */
    public function create(array $data): Company;

    /**
     * Update an existing company record.
     *
     * @param string $id
     * @param array<string, mixed> $data
     * @return Company|null
     */
    public function update(string $id, array $data): ?Company;

    /**
     * Delete a company record.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Paginate company records.
     *
     * @param int $page
     * @return PaginationInterface
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a company by a specific field.
     *
     * @param string $field
     * @param string|int $value
     * @return Company|null
     */
    public function showCompany(string $field, string | int $value): ?Company;
}
