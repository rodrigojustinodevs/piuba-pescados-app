<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class WaterQualityRepository implements WaterQualityRepositoryInterface
{
    /**
     * Create a new waterQuality.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): WaterQuality
    {
        return WaterQuality::create($data);
    }

    /**
     * Update an existing waterQuality.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?WaterQuality
    {
        $waterQuality = WaterQuality::find($id);

        if ($waterQuality) {
            $waterQuality->update($data);

            return $waterQuality;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<WaterQuality> $paginator */
        $paginator = WaterQuality::with([
            'tank:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show waterQuality by field and value.
     */
    public function showWaterQuality(string $field, string | int $value): ?WaterQuality
    {
        return WaterQuality::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $waterQuality = WaterQuality::find($id);

        if (! $waterQuality) {
            return false;
        }

        return (bool) $waterQuality->delete();
    }
}
