<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BiometryRepository implements BiometryRepositoryInterface
{
    /**
     * Create a new biometry.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Biometry
    {
        return Biometry::create($data);
    }

    /**
     * Update an existing biometry.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Biometry
    {
        $biometry = Biometry::find($id);

        if ($biometry) {
            $biometry->update($data);

            return $biometry;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Biometry> $paginator */
        $paginator = Biometry::with([
            'batch:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show biometry by field and value.
     */
    public function showBiometry(string $field, string | int $value): ?Biometry
    {
        return Biometry::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $biometry = Biometry::find($id);

        if (! $biometry) {
            return false;
        }

        return (bool) $biometry->delete();
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
}
