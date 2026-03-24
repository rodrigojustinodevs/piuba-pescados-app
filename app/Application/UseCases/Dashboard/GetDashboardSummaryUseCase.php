<?php

declare(strict_types=1);

namespace App\Application\UseCases\Dashboard;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\DashboardSummaryDTO;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use App\Domain\Repositories\SensorRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;

final readonly class GetDashboardSummaryUseCase
{
    public function __construct(
        private CompanyResolverInterface $companyResolver,
        private GetDashboardAlertsUseCase $alertsUseCase,
        private TankRepositoryInterface $tankRepository,
        private SensorReadingRepositoryInterface $sensorReadingRepository,
        private StockRepositoryInterface $stockRepository,
        private SensorRepositoryInterface $sensorRepository,
    ) {
    }

    public function execute(): DashboardSummaryDTO
    {
        $companyId = $this->companyResolver->resolve();

        $totalTanks = $this->tankRepository->countActiveTanks($companyId);

        $readingsLast24h = $this->sensorReadingRepository->countReadingsLast24h($companyId);

        $stocksBelowMinimum = $this->stockRepository->countStocksBelowMinimum($companyId);

        $inactiveSensors = $this->sensorRepository->countInactiveSensors($companyId);

        $alerts          = $this->alertsUseCase->execute();
        $tanksWithAlerts = count($alerts);
        $criticalAlerts  = count(array_filter(
            $alerts,
            static fn (\App\Application\DTOs\TankAlertDTO $a): bool => $a->severity() === 'critical',
        ));

        return new DashboardSummaryDTO(
            totalTanks:         $totalTanks,
            tanksWithAlerts:    $tanksWithAlerts,
            criticalAlerts:     $criticalAlerts,
            readingsLast24h:    $readingsLast24h,
            stocksBelowMinimum: $stocksBelowMinimum,
            inactiveSensors:    $inactiveSensors,
        );
    }
}
