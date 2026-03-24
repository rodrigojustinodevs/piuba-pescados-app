<?php

declare(strict_types=1);

namespace App\Application\UseCases\Dashboard;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\WaterQualityDataPoint;
use App\Application\DTOs\WaterQualityTrendDTO;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class GetWaterQualityTrendsUseCase
{
    private const array ALLOWED_PARAMETERS = [
        'temperature',
        'ph',
        'dissolved_oxygen',
        'ammonia',
        'salinity',
        'turbidity',
    ];

    private const array ALLOWED_PERIODS       = ['24h', '7d', '30d'];
    private const array ALLOWED_GRANULARITIES = ['hour', 'day'];

    private const array PARAMETER_UNITS = [
        'temperature'      => '°C',
        'ph'               => 'pH',
        'dissolved_oxygen' => 'mg/L',
        'ammonia'          => 'mg/L',
        'salinity'         => 'ppt',
        'turbidity'        => 'NTU',
    ];

    public function __construct(
        private CompanyResolverInterface $companyResolver,
        private WaterQualityRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     company_id?: string|null,
     *     tank_id?: string|null,
     *     parameter?: string|null,
     *     period?: string|null,
     *     granularity?: string|null
     * } $filters
     * @return array<int, WaterQualityTrendDTO>
     */
    public function execute(array $filters = []): array
    {
        $companyId = $this->companyResolver->resolve(
            hint: $filters['company_id'] ?? null,
        );

        $parameter = $this->resolveAllowedValue(
            $filters['parameter'] ?? null,
            self::ALLOWED_PARAMETERS,
            'temperature'
        );

        $period = $this->resolveAllowedValue(
            $filters['period'] ?? null,
            self::ALLOWED_PERIODS,
            '7d'
        );

        $granularity = $this->resolveAllowedValue(
            $filters['granularity'] ?? null,
            self::ALLOWED_GRANULARITIES,
            $period === '24h' ? 'hour' : 'day'
        );

        [$from, $to] = $this->resolvePeriod($period);

        $rows = collect(
            $this->repository->getTrends(
                $companyId,
                $parameter,
                $from,
                $to,
                $granularity,
                $filters['tank_id'] ?? null
            )
        );

        return $rows->groupBy('tank_id')
            ->map(
                fn (Collection $tankRows): WaterQualityTrendDTO => $this
                    ->mapTrend($tankRows, $parameter)
            )
            ->values()
            ->all();
    }

    /** @param Collection<int, object> $tankRows */
    private function mapTrend(Collection $tankRows, string $parameter): WaterQualityTrendDTO
    {
        $first = $tankRows->first();

        $dataPoints = $tankRows->map(
            fn ($row): WaterQualityDataPoint => new WaterQualityDataPoint(
                timestamp: (string) $row->period,
                value: round((float) $row->avg_value, 3),
                avg: round((float) $row->avg_value, 3),
                min: round((float) $row->min_value, 3),
                max: round((float) $row->max_value, 3),
            )
        )->values()->all();

        $values = $tankRows->pluck('avg_value')->map(fn ($v): float => (float) $v);

        return new WaterQualityTrendDTO(
            tankId: (string) $first->tank_id,
            tankName: (string) $first->tank_name,
            parameter: $parameter,
            unit: self::PARAMETER_UNITS[$parameter],
            dataPoints: $dataPoints,
            currentValue: round((float) $tankRows->last()->avg_value, 3),
            minValue: round($values->min(), 3),
            maxValue: round($values->max(), 3),
            avgValue: round($values->avg(), 3),
        );
    }

    /** @return array{0: string, 1: string} */
    private function resolvePeriod(string $period): array
    {
        $now = now();

        $from = match ($period) {
            '24h'   => $now->copy()->subHours(24),
            '7d'    => $now->copy()->subDays(7),
            '30d'   => $now->copy()->subDays(30),
            default => $now->copy()->subDays(7),
        };

        return [$from->toDateTimeString(), $now->toDateTimeString()];
    }

    /** @param array<int, string> $allowed */
    private function resolveAllowedValue(?string $value, array $allowed, string $default): string
    {
        return ($value && in_array($value, $allowed, true)) ? $value : $default;
    }
}
