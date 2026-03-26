<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Biometry;

interface BiometryRepositoryInterface
{
    /**
     * @param array{
     *     batch_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Biometry;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Biometry;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Biometry;

    public function delete(string $id): bool;

    public function findLatestByBatch(string $batchId): ?Biometry;

    public function findLatestBeforeDate(string $batchId, string $date): ?Biometry;

    public function previousAverageWeight(string $batchId, string $date): float;

    /**
     * Check if a biometry exists for the given batch and date.
     */
    public function existsByBatchAndDate(string $batchId, string $date, ?string $excludeId = null): bool;
}
