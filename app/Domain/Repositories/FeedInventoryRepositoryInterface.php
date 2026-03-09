<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\FeedInventory;

interface FeedInventoryRepositoryInterface
{
    /**
     * Create a new feed inventory record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedInventory;

    /**
     * Update an existing feed inventory record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FeedInventory;

    /**
     * Delete a feed inventory record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate feed inventory records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a feed inventory by a specific field.
     */
    public function showFeedInventory(string $field, string | int $value): ?FeedInventory;

    /**
     * Find a feed inventory by company and feed type.
     */
    public function findByCompanyAndFeedType(string $companyId, string $feedType): ?FeedInventory;
}
