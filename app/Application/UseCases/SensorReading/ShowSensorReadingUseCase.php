<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class ShowSensorReadingUseCase
{
    public function __construct(
        protected SensorReadingRepositoryInterface $sensorReadingRepository
    ) {
    }

    public function execute(string $id): ?SensorReadingDTO
    {
        $reading = $this->sensorReadingRepository->showSensorReading('id', $id);

        if (! $reading instanceof SensorReading) {
            throw new RuntimeException('Sensor reading not found');
        }

        $readingDate = $reading->reading_date instanceof Carbon
            ? $reading->reading_date
            : Carbon::parse($reading->reading_date);

        return new SensorReadingDTO(
            id: $reading->id,
            value: (float) $reading->value,
            readingDate: $readingDate->toDateTimeString(),
            sensor: isset($readingDate['sensor']) ? [
                'id'         => $readingDate['sensor']['id'] ?? null,
                'sensorType' => $readingDate['sensor']['sensor_type'] ?? null,
            ] : null,
            createdAt: $reading->created_at?->toDateTimeString(),
            updatedAt: $reading->updated_at?->toDateTimeString(),
        );
    }
}
