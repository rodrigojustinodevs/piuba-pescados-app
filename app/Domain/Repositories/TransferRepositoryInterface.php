<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Transfer;

interface TransferRepositoryInterface
{
    /**
     * Create a new transfer record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Transfer;

    /**
     * Update an existing transfer record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Transfer;

    /**
     * Delete a transfer record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate transfer records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a transfer by a specific field.
     */
    public function showTransfer(string $field, string | int $value): ?Transfer;
}
