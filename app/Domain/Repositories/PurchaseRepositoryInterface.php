<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Purchase;

interface PurchaseRepositoryInterface
{
    /**
     * Create a new purchase record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Purchase;

    /**
     * Update an existing purchase record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Purchase;

    /**
     * Delete a purchase record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate purchase records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a purchase by a specific field.
     */
    public function showPurchase(string $field, string | int $value): ?Purchase;
}
