<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SupplyInputDTO;
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

    public function create(SupplyInputDTO $dto): Supply;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Supply;
}
