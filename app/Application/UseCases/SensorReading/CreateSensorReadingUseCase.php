<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Exceptions\SensorNotAssignedToCompanyException;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class CreateSensorReadingUseCase
{
    public function __construct(
        private readonly SensorReadingRepositoryInterface $repository,
        private readonly SensorRepositoryInterface        $sensorRepository,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): SensorReading
    {
        $sensor = $this->sensorRepository->findOrFail($data['sensor_id']);

        if ($sensor->company_id === null) {
            throw new SensorNotAssignedToCompanyException((string) $sensor->id);
        }

        $data['company_id'] = (string) $sensor->company_id;
        $data['type'] = (string) 'manual';

        $dto = SensorReadingDTO::fromArray($data);

        $reading = DB::transaction(
            fn (): SensorReading => $this->repository->create($dto)
        );

        return $reading->load('sensor.tank');
    }
}
