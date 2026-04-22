<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\TankInputDTO;
use App\Domain\Models\Tank;

interface TankRepositoryInterface
{
    /**
     * @param array{
     *     companyId?: string|null,
     *     status?: string|null,
     *     tankTypeId?: string|null,
     *     perPage?: int,
     *     search?: string|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    /**
     * @param array{
     *     company_id?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginateWithoutBatches(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Tank;

    public function showTank(string $field, string | int $value): ?Tank;

    public function create(TankInputDTO $dto): Tank;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Tank;

    public function delete(string $id): void;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAllByCompany(string $companyId): array;

    public function countActiveTanks(string $companyId): int;
}
