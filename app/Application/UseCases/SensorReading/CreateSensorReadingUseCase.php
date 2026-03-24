<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSensorReadingUseCase
{
    public function __construct(
        private SensorReadingRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(array $data): SensorReading
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = SensorReadingDTO::fromArray($data);

        return DB::transaction(
            fn (): SensorReading => $this->repository->create($dto)->load('sensor.tank'),
        );
    }
}
