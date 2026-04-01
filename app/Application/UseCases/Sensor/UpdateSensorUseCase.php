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
        $sensor = $this->sensorRepository->update($id, $this->toPersistenceKeys($data));

        return $sensor->load('tank');
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function toPersistenceKeys(array $data): array
    {
        $aliases = [
            'tankId'           => 'tank_id',
            'sensorType'       => 'sensor_type',
            'installationDate' => 'installation_date',
        ];

        $out = [];

        foreach ($data as $key => $value) {
            $out[$aliases[$key] ?? $key] = $value;
        }

        return $out;
    }
}
