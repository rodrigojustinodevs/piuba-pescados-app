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
     *     search?: string|null,
     *     tankId?: string|null,
     *     sensorType?: string|null,
     *     status?: string|null,
     *     perPage?: int|string|null,
     *     page?: int|string|null,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->sensorRepository->paginate($filters);
    }
}
