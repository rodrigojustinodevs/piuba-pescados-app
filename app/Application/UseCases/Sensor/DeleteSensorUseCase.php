<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSensorUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->sensorRepository->delete($id));
    }
}
