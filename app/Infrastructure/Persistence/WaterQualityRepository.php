<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Illuminate\Support\Facades\DB;

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

    /** @return array<string, object> */
    public function getLatestByTank(string $companyId): array
    {
        return DB::table('water_qualities as wq')
            ->join('tanks', 'tanks.id', '=', 'wq.tank_id')
            ->whereIn('wq.id', function ($sub) use ($companyId): void {
                $sub->from('water_qualities')
                    ->selectRaw('MAX(id)')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->groupBy('tank_id');
            })
            ->select([
                'wq.tank_id',
                'tanks.name as tank_name',
                'wq.ph',
                'wq.dissolved_oxygen',
                'wq.ammonia',
                'wq.temperature',
                'wq.measured_at',
            ])
            ->get()
            ->keyBy('tank_id')
            ->toArray();
    }

    /**
     * @return array<int, object>
     */
    public function getTrends(
        string $companyId,
        string $parameter,
        string $dateFrom,
        string $dateTo,
        string $granularity,
        ?string $tankId = null
    ): array {
        $periodExpression = $granularity === 'hour'
            ? "DATE_FORMAT(wq.measured_at, '%Y-%m-%d %H:00:00')"
            : 'DATE(wq.measured_at)';

        return DB::table('water_qualities as wq')
            ->join('tanks', 'tanks.id', '=', 'wq.tank_id')
            ->where('wq.company_id', $companyId)
            ->whereBetween('wq.measured_at', [$dateFrom, $dateTo])
            ->when(
                $tankId,
                fn ($q) => $q->where('wq.tank_id', $tankId)
            )
            ->whereNotNull("wq.{$parameter}")
            ->select([
                'wq.tank_id',
                'tanks.name as tank_name',
                DB::raw("{$periodExpression} as period"),
                DB::raw("AVG(wq.{$parameter}) as avg_value"),
                DB::raw("MIN(wq.{$parameter}) as min_value"),
                DB::raw("MAX(wq.{$parameter}) as max_value"),
            ])
            ->groupBy('wq.tank_id', 'tanks.name', DB::raw($periodExpression))
            ->orderBy('wq.tank_id')
            ->orderBy('period')
            ->get()
            ->all();
    }
}
