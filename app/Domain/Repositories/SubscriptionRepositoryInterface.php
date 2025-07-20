<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Subscription;

    public function findById(string $id): ?Subscription;

    /**
     * Paginate supplier records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Subscription;

    public function delete(string $id): bool;
}
