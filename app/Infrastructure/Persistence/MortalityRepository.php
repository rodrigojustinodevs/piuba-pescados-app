<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\MortalityInputDTO;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class MortalityRepository implements MortalityRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,tank_id,initial_quantity,status',
    ];

    /**
     * @param array{
     *     batch_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     cause?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Mortality::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['batch_id']),
                static fn ($q) => $q->where('batch_id', $filters['batch_id']),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('mortality_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('mortality_date', '<=', $filters['date_to']),
            )
            ->when(
                ! empty($filters['cause']),
                static fn ($q) => $q->where('cause', 'like', '%' . $filters['cause'] . '%'),
            )
            ->latest('mortality_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Mortality
    {
        return Mortality::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function create(MortalityInputDTO $dto): Mortality
    {
        /** @var Mortality $mortality */
        $mortality = Mortality::create($dto->toPersistence());

        return $mortality->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Mortality
    {
        $mortality = $this->findOrFail($id);
        $mortality->update($attributes);

        return $mortality->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function totalMortalities(string $batchId, ?string $excludeMortalityId = null): int
    {
        return (int) Mortality::where('batch_id', $batchId)
            ->when($excludeMortalityId, static function ($query, string $id): void {
                $query->where('id', '!=', $id);
            })
            ->sum('quantity');
    }
}
