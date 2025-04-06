<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class UpdateSensorReadingUseCase
{
    public function __construct(
        protected SensorReadingRepositoryInterface $sensorReadingRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): SensorReadingDTO
    {
        $reading = $this->sensorReadingRepository->update($id, $data);

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
            sensor: $reading->sensor !== null ? [
                'id'         => $reading->sensor->id ?? null,
                'sensorType' => $reading->sensor->sensor_type ?? null,
            ] : null,
            createdAt: $reading->created_at?->toDateTimeString(),
            updatedAt: $reading->updated_at?->toDateTimeString(),
        );
    }
}
