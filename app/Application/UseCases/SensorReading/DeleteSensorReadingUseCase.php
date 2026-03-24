<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Domain\Repositories\SensorReadingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteSensorReadingUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}
