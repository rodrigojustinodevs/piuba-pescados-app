<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\GrowthCurveInputDTO;
use App\Domain\Models\GrowthCurve;

interface GrowthCurveRepositoryInterface
{
    /**
     * @param array{
     *     batch_id?: string|null,
     *     company_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): GrowthCurve;

    public function create(GrowthCurveInputDTO $dto): GrowthCurve;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): GrowthCurve;

    public function delete(string $id): bool;

    /**
     * Find a growth curve by a specific field.
     */
    public function showGrowthCurve(string $field, string | int $value): ?GrowthCurve;
}
