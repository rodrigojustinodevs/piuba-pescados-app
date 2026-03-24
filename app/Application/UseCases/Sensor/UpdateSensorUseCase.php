<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Domain\Models\Sensor;
use App\Domain\Repositories\SensorRepositoryInterface;

class UpdateSensorUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Sensor
    {
        $sensor = $this->sensorRepository->update($id, $data);

        return $sensor->load('tank');
    }
}
