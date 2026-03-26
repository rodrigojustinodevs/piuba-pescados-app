<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\FeedInventory;

interface FeedInventoryRepositoryInterface
{
    /**
     * @param array{
     *     company_id?: string|null,
     *     feed_type?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): FeedInventory;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedInventory;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FeedInventory;

    public function delete(string $id): bool;

    public function findByCompanyAndFeedType(string $companyId, string $feedType): ?FeedInventory;
}
