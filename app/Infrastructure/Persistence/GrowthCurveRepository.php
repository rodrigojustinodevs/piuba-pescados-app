<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GrowthCurveRepository implements GrowthCurveRepositoryInterface
{
    /**
     * Create a new growthCurve.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): GrowthCurve
    {
        return GrowthCurve::create($data);
    }

    /**
     * Update an existing growthCurve.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?GrowthCurve
    {
        $growthCurve = GrowthCurve::find($id);

        if ($growthCurve) {
            $growthCurve->update($data);

            return $growthCurve;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<GrowthCurve> $paginator */
        $paginator = GrowthCurve::paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show growthCurve by field and value.
     */
    public function showGrowthCurve(string $field, string | int $value): ?GrowthCurve
    {
        return GrowthCurve::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $growthCurve = GrowthCurve::find($id);

        if (! $growthCurve) {
            return false;
        }

        return (bool) $growthCurve->delete();
    }
}
