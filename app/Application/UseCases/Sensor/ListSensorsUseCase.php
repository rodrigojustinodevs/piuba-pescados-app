<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorRepositoryInterface;

class ListSensorsUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository,
        protected CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     tank_id?: string|null,
     *     sensor_type?: string|null,
     *     status?: string|null,
     *     per_page?: int|string|null,
     *     page?: int|string|null,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->sensorRepository->paginate($filters);
    }
}
