<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SupplyInputDTO;
use App\Domain\Models\Supply;

interface SupplyRepositoryInterface
{
    /**
     * @param array{
     *     companyId?: string|null,
     *     perPage?: int,
     *     category?: string|null,
     *     status?: string|null,
     *     isProduct?: bool|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Supply;

    public function create(SupplyInputDTO $dto): Supply;

    public function update(string $id, SupplyInputDTO $dto): Supply;

    public function delete(string $id): bool;
}
