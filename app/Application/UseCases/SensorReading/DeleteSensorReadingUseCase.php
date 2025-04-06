<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Domain\Repositories\SensorReadingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSensorReadingUseCase
{
    public function __construct(
        protected SensorReadingRepositoryInterface $sensorReadingRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->sensorReadingRepository->delete($id));
    }
}
