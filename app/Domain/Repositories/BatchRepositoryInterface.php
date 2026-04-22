<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\BatchInputDTO;
use App\Domain\Models\Batch;

interface BatchRepositoryInterface
{
    /**
     * @param array{
     *     status?: string|null,
     *     tank_id?: string|null,
     *     species?: string|null,
     *     perPage?: int,
     *     companyId?: string|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Batch;

    /**
     * Find a batch by a specific field.
     * Kept for backward-compatibility with external modules (Feeding, Biometry, Transfer, etc.).
     */
    public function showBatch(string $field, string | int $value): ?Batch;

    public function create(BatchInputDTO $dto): Batch;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Batch;

    public function delete(string $id): bool;

    /**
     * Check if there is another active batch in the tank.
     */
    public function hasActiveBatchInTank(string $tankId, ?string $exceptBatchId = null): bool;
}
