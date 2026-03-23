<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;

final readonly class ShowSensorReadingUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): SensorReading
    {
        return $this->repository->findOrFail($id);
    }
}
