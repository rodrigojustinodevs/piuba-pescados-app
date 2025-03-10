<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Company;
use App\Domain\Repositories\PaginationInterface;

interface CompanyRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Company;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Company;

    public function delete(array $attributes): bool;

    public function findAll(): array;

    public function paginate(int $page = 25): PaginationInterface;

    public function showCompany(string $field, string|int $value): ?Company;
}
