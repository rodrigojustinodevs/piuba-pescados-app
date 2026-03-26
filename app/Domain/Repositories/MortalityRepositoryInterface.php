<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\MortalityInputDTO;
use App\Domain\Models\Mortality;

interface MortalityRepositoryInterface
{
    /**
     * @param array{
     *     batch_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     cause?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    public function findOrFail(string $id): Mortality;

    public function create(MortalityInputDTO $dto): Mortality;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Mortality;

    public function delete(string $id): bool;

    /**
     * Sum of mortality quantities for a given batch, optionally excluding one record.
     */
    public function totalMortalities(string $batchId, ?string $excludeMortalityId = null): int;
}
