<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListSensorReadingsUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $sensorReadingRepository,
    ) {
    }

    /**
     * @param array{
     *     sensorId?: string|null,
     *     type?: string|null,
     *     tankId?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int|string|null,
     *     page?: int|string|null,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->sensorReadingRepository->paginate($filters);
    }
}
