<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\BatchInputDTO;
use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class BatchRepository implements BatchRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'tank:id,name,company_id,capacity_liters',
    ];

    /**
     * @param array{
     *     status?: string|null,
     *     tank_id?: string|null,
     *     species?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Batch::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', BatchStatus::from($filters['status'])->value),
            )
            ->when(
                ! empty($filters['tank_id']),
                static fn ($q) => $q->where('tank_id', $filters['tank_id']),
            )
            ->when(
                ! empty($filters['species']),
                static fn ($q) => $q->where('species', 'like', '%' . $filters['species'] . '%'),
            )
            ->latest('entry_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Batch
    {
        return Batch::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function showBatch(string $field, string | int $value): ?Batch
    {
        return Batch::with(self::DEFAULT_RELATIONS)
            ->where($field, $value)
            ->first();
    }

    public function create(BatchInputDTO $dto): Batch
    {
        /** @var Batch $batch */
        $batch = Batch::create($dto->toPersistence());

        return $batch->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Batch
    {
        $batch = $this->findOrFail($id);
        $batch->update($attributes);

        return $batch->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function hasActiveBatchInTank(string $tankId, ?string $exceptBatchId = null): bool
    {
        return Batch::query()
            ->where('tank_id', $tankId)
            ->where('status', BatchStatus::ACTIVE->value)
            ->when($exceptBatchId, static function ($query, string $id): void {
                $query->where('id', '!=', $id);
            })
            ->exists();
    }
}
