<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Application\DTOs\SensorDTO;
use App\Domain\Enums\SensorType;
use App\Domain\Enums\Status;
use App\Domain\Repositories\SensorRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateSensorUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): SensorDTO
    {
        return DB::transaction(function () use ($data): SensorDTO {
            $sensor = $this->sensorRepository->create($data);

            $installationDate = $sensor->installation_date instanceof Carbon
                ? $sensor->installation_date
                : Carbon::parse($sensor->installation_date);

            return new SensorDTO(
                id: $sensor->id,
                sensorType: SensorType::from($sensor->sensor_type),
                status: Status::from($sensor->status),
                tank: [
                    'id'   => $sensor->tank->id ?? '',
                    'name' => $sensor->tank->name ?? '',
                ],
                installationDate: $installationDate->toDateString(),
                createdAt: $sensor->created_at?->toDateTimeString(),
                updatedAt: $sensor->updated_at?->toDateTimeString()
            );
        });
    }
}
