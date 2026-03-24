<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorReadingRepositoryInterface;

final readonly class ListSensorReadingsUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $sensorReadingRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     sensor_id?: string|null,
     *     tank_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int|string|null,
     *     page?: int|string|null,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->sensorReadingRepository->paginate($filters);
    }
}
