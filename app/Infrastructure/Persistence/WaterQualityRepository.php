<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\WaterQualityRepositoryInterface;

class WaterQualityRepository implements WaterQualityRepositoryInterface
{
    /**
     * Create a new waterQuality.
     *
     */
    public function create(WaterQualityDTO $dto): WaterQuality
    {
        /** @var WaterQuality */
        return WaterQuality::create($dto->toPersistence());
    }

    /**
     * Update an existing waterQuality.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): WaterQuality
    {
        $waterQuality = $this->findOrFail($id);
        $waterQuality->update(array_filter($attributes, static fn ($v): bool => $v !== null));

        return $waterQuality->refresh();
    }

    /**
     * Get paginated records.
     *
     * @param array{
     *     company_id: string,
     *     tank_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = WaterQuality::with(['tank:id,name'])
            // ✅ Multi-tenancy via tank.company_id
            ->whereHas('tank', static fn ($q) => $q->where('company_id', $filters['company_id']))
            ->when(
                ! empty($filters['tank_id']),
                static fn ($q) => $q->where('tank_id', $filters['tank_id']),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('measured_at', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('measured_at', '<=', $filters['date_to']),
            )
            ->latest('measured_at')
            ->paginate((int) ($filters['per_page'] ?? 25));

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
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findOrFail(string $id): WaterQuality
    {
        return WaterQuality::findOrFail($id);
    }

    public function findByCompany(string $companyId): ?WaterQuality
    {
        return WaterQuality::where('company_id', $companyId)->first();
    }
}
