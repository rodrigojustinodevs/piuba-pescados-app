<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Stocking;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class StockingRepository implements StockingRepositoryInterface
{
    /**
     * Create a new stocking.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Stocking
    {
        return Stocking::create($data);
    }

    /**
     * Update an existing stocking.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Stocking
    {
        $stocking = Stocking::find($id);

        if ($stocking) {
            $stocking->update($data);

            return $stocking;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Stocking> $paginator */
        $paginator = Stocking::with([
            'batche:id',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show stocking by field and value.
     */
    public function showStocking(string $field, string | int $value): ?Stocking
    {
        return Stocking::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $stocking = Stocking::find($id);

        if (! $stocking) {
            return false;
        }

        return (bool) $stocking->delete();
    }
}
