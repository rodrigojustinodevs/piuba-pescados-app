<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\CompanyInputDTO;
use App\Domain\Models\Company;

interface CompanyRepositoryInterface
{
    public function create(CompanyInputDTO $dto): Company;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Company;

    /**
     * Delete a company record.
     */
    public function delete(string $id): void;

    /**
     * @param array{
     *     per_page?: int,
     *     search?: string|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Company;

    public function showCompany(string $field, string | int $value): ?Company;
}
