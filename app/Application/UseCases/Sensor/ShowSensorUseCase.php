<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Domain\Models\Sensor;
use App\Domain\Repositories\SensorRepositoryInterface;
use RuntimeException;

class ShowSensorUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository
    ) {
    }

    public function execute(string $id): Sensor
    {
        $sensor = $this->sensorRepository->showSensor('id', $id);

        if (! $sensor instanceof Sensor) {
            throw new RuntimeException('Sensor not found');
        }

        return $sensor->loadMissing('tank');
    }
}
