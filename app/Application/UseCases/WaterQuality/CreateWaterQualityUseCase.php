<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateWaterQualityUseCase
{
    public function __construct(
        private WaterQualityRepositoryInterface $repository,
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): WaterQuality
    {
        $tank = $this->tankRepository->findOrFail($data['tank_id']);

        $data['company_id'] = (string) $tank->company_id;

        $dto = WaterQualityDTO::fromArray($data);

        $record = DB::transaction(
            fn (): WaterQuality => $this->repository->create($dto)
        );

        return $record->load('tank');
    }
}
