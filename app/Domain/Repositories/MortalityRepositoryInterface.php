<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Mortality;

interface MortalityRepositoryInterface
{
    /**
     * Create a new mortality record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Mortality;

    /**
     * Update an existing mortality record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Mortality;

    /**
     * Delete a mortality record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate mortality records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a mortality by a specific field.
     */
    public function showMortality(string $field, string | int $value): ?Mortality;
}
