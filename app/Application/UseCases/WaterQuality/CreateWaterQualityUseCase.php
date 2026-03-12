<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateWaterQualityUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): WaterQualityDTO
    {
        return DB::transaction(function () use ($data): WaterQualityDTO {
            $waterQuality = $this->waterQualityRepository->create($data);

            $measuredAt = $waterQuality->measured_at instanceof Carbon
                ? $waterQuality->measured_at
                : Carbon::parse($waterQuality->measured_at);

            return new WaterQualityDTO(
                id: $waterQuality->id,
                ph: (float) $waterQuality->ph,
                dissolvedOxygen: (float) $waterQuality->dissolved_oxygen,
                temperature: (float) $waterQuality->temperature,
                ammonia: (float) $waterQuality->ammonia,
                tank: [
                    'id'   => $waterQuality->tank->id ?? '',
                    'name' => $waterQuality->tank->name ?? '',
                ],
                measuredAt: $measuredAt->toDateString(),
                createdAt: $waterQuality->created_at?->toDateTimeString(),
                updatedAt: $waterQuality->updated_at?->toDateTimeString()
            );
        });
    }
}
