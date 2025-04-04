<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Application\DTOs\SensorDTO;
use App\Domain\Enums\SensorType;
use App\Domain\Enums\Status;
use App\Domain\Models\Sensor;
use App\Domain\Repositories\SensorRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class ShowSensorUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository
    ) {
    }

    public function execute(string $id): ?SensorDTO
    {
        $sensor = $this->sensorRepository->showSensor('id', $id);

        if (! $sensor instanceof Sensor) {
            throw new RuntimeException('Sensor not found');
        }

        $installationDate = $sensor->installation_date instanceof Carbon
            ? $sensor->installation_date
            : Carbon::parse($sensor->installation_date);

        return new SensorDTO(
            id: $sensor->id,
            sensorType: SensorType::from($sensor->sensor_type),
            installationDate: $installationDate->toDateString(),
            status: Status::from($sensor->status),
            tank: [
                'id'   => $sensor->tank->id ?? '',
                'name' => $sensor->tank->name ?? '',
            ],
            createdAt: $sensor->created_at?->toDateTimeString(),
            updatedAt: $sensor->updated_at?->toDateTimeString()
        );
    }
}
