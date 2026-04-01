<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Supply;

interface SupplyRepositoryInterface
{
    /**
     * @param array{
     *     company_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Supply;
}
