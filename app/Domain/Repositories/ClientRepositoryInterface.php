<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Client;

interface ClientRepositoryInterface
{
    /**
     * Create a new financial transaction record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Client;

    /**
     * Update an existing financial transaction record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Client;

    /**
     * Delete a financial transaction record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate financial transaction records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a financial transaction by a specific field.
     */
    public function showClient(string $field, string | int $value): ?Client;
}
