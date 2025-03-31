<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\FeedControl;

interface FeedControlRepositoryInterface
{
    /**
     * Create a new feedcontrol record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedControl;

    /**
     * Update an existing feeding record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FeedControl;

    /**
     * Delete a feedcontrol record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate feedcontrol records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a feedcontrol by a specific field.
     */
    public function showFeedControl(string $field, string | int $value): ?FeedControl;
}
