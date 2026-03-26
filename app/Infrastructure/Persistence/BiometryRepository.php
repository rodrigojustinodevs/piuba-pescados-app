<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class BiometryRepository implements BiometryRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,name,tank_id,initial_quantity,status',
    ];

    /**
     * @param array{
     *     batch_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Biometry::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['batch_id']),
                static fn ($q) => $q->where('batch_id', $filters['batch_id']),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('biometry_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('biometry_date', '<=', $filters['date_to']),
            )
            ->latest('biometry_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Biometry
    {
        return Biometry::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Biometry
    {
        /** @var Biometry $biometry */
        $biometry = Biometry::create($data);

        return $biometry->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Biometry
    {
        $biometry = $this->findOrFail($id);
        $biometry->update($attributes);

        return $biometry->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findLatestByBatch(string $batchId): ?Biometry
    {
        return Biometry::query()
            ->where('batch_id', $batchId)
            ->orderByDesc('biometry_date')
            ->first();
    }

    public function findLatestBeforeDate(string $batchId, string $date): ?Biometry
    {
        return Biometry::query()
            ->where('batch_id', $batchId)
            ->where('biometry_date', '<', $date)
            ->orderByDesc('biometry_date')
            ->first();
    }

    public function previousAverageWeight(string $batchId, string $date): float
    {
        $previous = $this->findLatestBeforeDate($batchId, $date);

        return $previous instanceof Biometry ? (float) $previous->average_weight : 0.0;
    }

    public function existsByBatchAndDate(string $batchId, string $date, ?string $excludeId = null): bool
    {
        return Biometry::query()
            ->where('batch_id', $batchId)
            ->whereDate('biometry_date', $date)
            ->when($excludeId, static function ($query, string $id): void {
                $query->where('id', '!=', $id);
            })
            ->exists();
    }
}
