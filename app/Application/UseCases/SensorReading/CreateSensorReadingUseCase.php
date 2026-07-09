<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Exceptions\SensorNotAssignedToCompanyException;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSensorReadingUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $repository,
        private SensorRepositoryInterface $sensorRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): SensorReading
    {
        $sensor = $this->sensorRepository->findOrFail($data['sensor_id']);

        if ($sensor->company_id === null) {
            throw new SensorNotAssignedToCompanyException((string) $sensor->id);
        }

        $data['company_id'] = (string) $sensor->company_id;
        $data['type']       = 'manual';

        $dto = SensorReadingDTO::fromArray($data);

        $reading = DB::transaction(
            fn (): SensorReading => $this->repository->create($dto)
        );

        return $reading->load('sensor.tank');
    }
}
