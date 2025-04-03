<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Biometry;

interface BiometryRepositoryInterface
{
    /**
     * Create a new biometry record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Biometry;

    /**
     * Update an existing biometry record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Biometry;

    /**
     * Delete a biometry record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate biometry records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a biometry by a specific field.
     */
    public function showBiometry(string $field, string | int $value): ?Biometry;
}
