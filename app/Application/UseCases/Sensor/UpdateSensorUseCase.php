<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SensorDTO;
use App\Domain\Models\Sensor;
use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSensorUseCase
{
    public function __construct(
        private SensorRepositoryInterface $sensorRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Sensor
    {
        $sensor = $this->sensorRepository->findOrFail($id);

        $data['tank_id'] ??= $data['tankId'] ?? (string) $sensor->tank_id;
        $data['sensor_type'] ??= $data['sensorType'] ?? (string) $sensor->sensor_type;
        $data['status'] ??= (string) $sensor->status;
        $data['company_id'] = $this->companyResolver->resolve(
            $data['company_id'] ?? $data['companyId'] ?? (string) $sensor->company_id,
        );

        return DB::transaction(function () use ($id, $data): Sensor {
            $dto    = SensorDTO::fromArray($data);
            $sensor = $this->sensorRepository->update($id, $dto->toPersistence());

            return $sensor->load('tank');
        });
    }
}
