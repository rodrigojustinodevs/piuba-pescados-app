<?php

declare(strict_types=1);

namespace App\Application\UseCases\Dashboard;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\TankAlertDTO;
use App\Domain\Repositories\SensorRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use App\Domain\ValueObjects\WaterQualityThresholds;

final readonly class GetDashboardAlertsUseCase
{
    public function __construct(
        private CompanyResolverInterface $companyResolver,
        private TankRepositoryInterface $tankRepository,
        private WaterQualityRepositoryInterface $waterQualityRepository,
        private StockRepositoryInterface $stockRepository,
        private SensorRepositoryInterface $sensorRepository,
    ) {
    }

    /** @return array<int, TankAlertDTO> */
    public function execute(): array
    {
        $companyId = $this->companyResolver->resolve();

        $tanks              = $this->tankRepository->findAllByCompany($companyId);
        $latestWaterQuality = $this->waterQualityRepository->getLatestByTank($companyId);
        $stockAlerts        = $this->stockRepository->getLowStockAlerts($companyId);
        $sensorAlerts       = $this->sensorRepository->getAlertByTank($companyId);

        return collect($tanks)->map(function (array $tank) use (
            $latestWaterQuality,
            $stockAlerts,
            $sensorAlerts,
        ): TankAlertDTO {
            $wq = $latestWaterQuality[$tank['id']] ?? null;

            $wqAlerts = $wq
                ? WaterQualityThresholds::evaluate(
                    ph: isset($wq->ph) ? (float) $wq->ph : null,
                    dissolvedOxygen: isset($wq->dissolved_oxygen) ? (float) $wq->dissolved_oxygen : null,
                    ammonia: isset($wq->ammonia) ? (float) $wq->ammonia : null,
                    temperature: isset($wq->temperature) ? (float) $wq->temperature : null,
                )
                : [];

            $tankSensorAlerts = $sensorAlerts[$tank['id']] ?? [];

            return new TankAlertDTO(
                tankId: $tank['id'],
                tankName: $tank['name'],
                waterQualityAlerts: $wqAlerts,
                stockAlerts: $stockAlerts,
                sensorAlerts: $tankSensorAlerts,
                lastMeasuredAt: $wq?->measured_at,
            );
        })
            ->filter(fn (TankAlertDTO $dto): bool => $dto->hasAlerts())
            ->sortBy(fn (TankAlertDTO $dto): int => match ($dto->severity()) {
                'critical' => 0,
                'warning'  => 1,
                default    => 2,
            })
            ->values()
            ->all();
    }
}
