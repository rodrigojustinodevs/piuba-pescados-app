<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;

final readonly class UpdateSensorReadingUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $sensorReadingRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): SensorReading
    {
        $reading = $this->sensorReadingRepository->update($id, $data);

        return $reading->loadMissing(['sensor.tank']);
    }
}
