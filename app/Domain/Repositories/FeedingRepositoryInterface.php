<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\FeedingInputDTO;
use App\Domain\Models\Feeding;

interface FeedingRepositoryInterface
{
    /**
     * @param array{
     *     batch_id?: string|null,
     *     feed_type?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Feeding;

    public function create(FeedingInputDTO $dto): Feeding;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Feeding;

    public function delete(string $id): bool;

    public function getDailyConsumptionAverage(string $companyId, string $feedType): float;

    public function findLatestByBatch(string $batchId): ?Feeding;

    public function existsByBatch(string $batchId): bool;

    public function totalFeedConsumedUntilDate(string $batchId, string $startDate, string $endDate): float;

    public function getTotalFeedConsumedByBatch(string $batchId): float;
}
