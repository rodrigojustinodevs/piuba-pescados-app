<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SensorDTO;
use App\Domain\Models\Sensor;
use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateSensorUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository,
        protected CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Sensor
    {
        return DB::transaction(function () use ($data): Sensor {
            $data['company_id'] = $this->companyResolver->resolve();
            $dto                = SensorDTO::fromArray($data);
            $sensor             = $this->sensorRepository->create($dto);

            return $sensor->load('tank');
        });
    }
}
