<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\GrowthCurveInputDTO;
use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class GrowthCurveRepository implements GrowthCurveRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,name,tank_id',
    ];

    /**
     * @param array{
     *     batch_id?: string|null,
     *     company_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = GrowthCurve::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['batch_id']),
                static fn ($q) => $q->where('batch_id', $filters['batch_id']),
            )
            ->when(
                ! empty($filters['company_id']),
                static function ($q) use ($filters): void {
                    $q->whereHas(
                        'batch.tank',
                        static fn ($tq) => $tq->where('company_id', $filters['company_id']),
                    );
                },
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): GrowthCurve
    {
        return GrowthCurve::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function create(GrowthCurveInputDTO $dto): GrowthCurve
    {
        /** @var GrowthCurve $growthCurve */
        $growthCurve = GrowthCurve::create($dto->toPersistence());

        return $growthCurve->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): GrowthCurve
    {
        $growthCurve = $this->findOrFail($id);
        $growthCurve->update($attributes);

        return $growthCurve->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function showGrowthCurve(string $field, string | int $value): ?GrowthCurve
    {
        return GrowthCurve::with(self::DEFAULT_RELATIONS)->where($field, $value)->first();
    }
}
