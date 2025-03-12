<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Batche;

interface BatcheRepositoryInterface
{
    /**
     * Create a new batche record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Batche;

    /**
     * Update an existing batche record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Batche;

    /**
     * Delete a batche record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate batche records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a batche by a specific field.
     */
    public function showBatche(string $field, string | int $value): ?Batche;
}
