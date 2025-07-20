<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Alert;

interface AlertRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Alert;

    public function findById(string $id): ?Alert;

    /**
     * Paginate supplier records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Alert;

    public function delete(string $id): bool;
}
