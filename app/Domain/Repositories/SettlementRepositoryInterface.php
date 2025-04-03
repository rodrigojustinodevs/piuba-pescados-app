<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Settlement;

interface SettlementRepositoryInterface
{
    /**
     * Create a new settlement record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Settlement;

    /**
     * Update an existing settlement record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Settlement;

    /**
     * Delete a settlement record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate settlement records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a settlement by a specific field.
     */
    public function showSettlement(string $field, string | int $value): ?Settlement;
}
