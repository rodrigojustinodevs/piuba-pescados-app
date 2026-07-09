<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Exceptions\SensorNotAssignedToCompanyException;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSensorReadingUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $repository,
        private SensorRepositoryInterface $sensorRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest (snake_case)
     */
    public function execute(string $id, array $data): SensorReading
    {
        $current = $this->repository->findOrFail($id);

        [$resolvedSensorId, $resolvedCompanyId] = $this->resolveSensorAndCompany($current, $data);

        $merged = array_merge(
            [
                'sensor_id'   => $resolvedSensorId,
                'company_id'  => $resolvedCompanyId,
                'value'       => (float) $current->value,
                'unit'        => (string) $current->unit,
                'measured_at' => $current->measured_at?->toDateTimeString(),
                'type'        => (string) $current->type,
                'notes'       => $current->notes,
            ],
            $data,
            [
                'sensor_id'  => $resolvedSensorId,
                'company_id' => $resolvedCompanyId,
            ]
        );

        $dto = SensorReadingDTO::fromArray($merged);

        $reading = DB::transaction(
            fn (): SensorReading => $this->repository->update($id, $dto->toUpdateAttributes())
        );

        return $reading->loadMissing('sensor.tank');
    }

    /**
     * @param  array<string, mixed> $data
     * @return array{string, string} [sensor_id, company_id]
     * @throws SensorNotAssignedToCompanyException
     */
    private function resolveSensorAndCompany(SensorReading $current, array $data): array
    {
        $newSensorId = $data['sensor_id'] ?? null;

        if ($newSensorId === null || $newSensorId === (string) $current->sensor_id) {
            return [(string) $current->sensor_id, (string) $current->company_id];
        }

        $newSensor = $this->sensorRepository->findOrFail($newSensorId);

        if ($newSensor->company_id === null) {
            throw new SensorNotAssignedToCompanyException($newSensorId);
        }

        return [(string) $newSensor->id, (string) $newSensor->company_id];
    }
}
