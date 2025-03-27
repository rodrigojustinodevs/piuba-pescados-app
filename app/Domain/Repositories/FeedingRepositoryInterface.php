<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Feeding;

interface FeedingRepositoryInterface
{
    /**
     * Create a new feeding record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Feeding;

    /**
     * Update an existing feeding record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Feeding;

    /**
     * Delete a feeding record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate feeding records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a feeding by a specific field.
     */
    public function showFeeding(string $field, string | int $value): ?Feeding;
}
