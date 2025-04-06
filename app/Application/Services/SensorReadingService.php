<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\SensorReadingDTO;
use App\Application\UseCases\SensorReading\CreateSensorReadingUseCase;
use App\Application\UseCases\SensorReading\DeleteSensorReadingUseCase;
use App\Application\UseCases\SensorReading\ListSensorReadingsUseCase;
use App\Application\UseCases\SensorReading\ShowSensorReadingUseCase;
use App\Application\UseCases\SensorReading\UpdateSensorReadingUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SensorReadingService
{
    public function __construct(
        protected CreateSensorReadingUseCase $createSensorReadingUseCase,
        protected ListSensorReadingsUseCase $listSensorReadingsUseCase,
        protected ShowSensorReadingUseCase $showSensorReadingUseCase,
        protected UpdateSensorReadingUseCase $updateSensorReadingUseCase,
        protected DeleteSensorReadingUseCase $deleteSensorReadingUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SensorReadingDTO
    {
        return $this->createSensorReadingUseCase->execute($data);
    }

    public function showAllSensorReadings(): AnonymousResourceCollection
    {
        return $this->listSensorReadingsUseCase->execute();
    }

    public function showSensorReading(string $id): ?SensorReadingDTO
    {
        return $this->showSensorReadingUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSensorReading(string $id, array $data): SensorReadingDTO
    {
        return $this->updateSensorReadingUseCase->execute($id, $data);
    }

    public function deleteSensorReading(string $id): bool
    {
        return $this->deleteSensorReadingUseCase->execute($id);
    }
}
