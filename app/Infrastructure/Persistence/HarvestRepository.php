<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Harvest;
use App\Domain\Repositories\HarvestRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class HarvestRepository implements HarvestRepositoryInterface
{
    /**
     * Create a new harvest.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Harvest
    {
        return Harvest::create($data);
    }

    /**
     * Update an existing harvest.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Harvest
    {
        $harvest = Harvest::find($id);

        if ($harvest) {
            $harvest->update($data);

            return $harvest;
        }

        return null;
    }

    /**
     * Get paginated.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Harvest> $paginator */
        $paginator = Harvest::with([
            'batche:id',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show harvest by field and value.
     */
    public function showHarvest(string $field, string | int $value): ?Harvest
    {
        return Harvest::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $harvest = Harvest::find($id);

        if (! $harvest) {
            return false;
        }

        return (bool)$harvest->delete();
    }
}
