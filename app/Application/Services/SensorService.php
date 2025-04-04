<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\SensorDTO;
use App\Application\UseCases\Sensor\CreateSensorUseCase;
use App\Application\UseCases\Sensor\DeleteSensorUseCase;
use App\Application\UseCases\Sensor\ListSensorsUseCase;
use App\Application\UseCases\Sensor\ShowSensorUseCase;
use App\Application\UseCases\Sensor\UpdateSensorUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SensorService
{
    public function __construct(
        protected CreateSensorUseCase $createSensorUseCase,
        protected ListSensorsUseCase $listSensorsUseCase,
        protected ShowSensorUseCase $showSensorUseCase,
        protected UpdateSensorUseCase $updateSensorUseCase,
        protected DeleteSensorUseCase $deleteSensorUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SensorDTO
    {
        return $this->createSensorUseCase->execute($data);
    }

    public function showAllSensors(): AnonymousResourceCollection
    {
        return $this->listSensorsUseCase->execute();
    }

    public function showSensor(string $id): ?SensorDTO
    {
        return $this->showSensorUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSensor(string $id, array $data): SensorDTO
    {
        return $this->updateSensorUseCase->execute($id, $data);
    }

    public function deleteSensor(string $id): bool
    {
        return $this->deleteSensorUseCase->execute($id);
    }
}
