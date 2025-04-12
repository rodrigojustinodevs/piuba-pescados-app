<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\CostAllocation;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CostAllocationRepository implements CostAllocationRepositoryInterface
{
    /**
     * Create a new costAllocation.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): CostAllocation
    {
        return CostAllocation::create($data);
    }

    /**
     * Update an existing costAllocation.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?CostAllocation
    {
        $costAllocation = CostAllocation::find($id);

        if ($costAllocation) {
            $costAllocation->update($data);

            return $costAllocation;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<CostAllocation> $paginator */
        $paginator = CostAllocation::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show costAllocation by field and value.
     */
    public function showCostAllocation(string $field, string | int $value): ?CostAllocation
    {
        return CostAllocation::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $costAllocation = CostAllocation::find($id);

        if (! $costAllocation) {
            return false;
        }

        return (bool) $costAllocation->delete();
    }
}
