<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Batch;

interface BatchRepositoryInterface
{
    /**
     * Create a new batch record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Batch;

    /**
     * Update an existing batch record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Batch;

    /**
     * Delete a batch record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate batch records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a batch by a specific field.
     */
    public function showBatch(string $field, string | int $value): ?Batch;

    /**
     * Check if there is another active batch in the tank.
     * Used to enforce: one tank can only have one active batch at a time.
     */
    public function hasActiveBatchInTank(string $tankId, ?string $exceptBatchId = null): bool;
}
