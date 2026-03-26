<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BatchRepository implements BatchRepositoryInterface
{
    /**
     * Create a new batch.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Batch
    {
        return Batch::create($data);
    }

    /**
     * Update an existing batch.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Batch
    {
        $batch = Batch::find($id);

        if ($batch) {
            $batch->update($data);

            return $batch;
        }

        return null;
    }

    /**
     * Get paginated batches.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Batch> $paginator */
        $paginator = Batch::with([
            'tank:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show batch by field and value.
     */
    public function showBatch(string $field, string | int $value): ?Batch
    {
        return Batch::with([
            'tank:id,name,company_id,capacity_liters',
        ])->where($field, $value)->first();
    }

    public function findOrFail(string $id): Batch
    {
        return Batch::with([
            'tank:id,name,company_id,capacity_liters',
        ])->findOrFail($id);
    }

    public function hasActiveBatchInTank(string $tankId, ?string $exceptBatchId = null): bool
    {
        $query = Batch::query()
            ->where('tank_id', $tankId)
            ->where('status', 'active');

        if ($exceptBatchId !== null && $exceptBatchId !== '') {
            $query->where('id', '!=', $exceptBatchId);
        }

        return $query->exists();
    }

    public function delete(string $id): bool
    {
        $batch = Batch::find($id);

        if (! $batch) {
            return false;
        }

        return (bool) $batch->delete();
    }
}
