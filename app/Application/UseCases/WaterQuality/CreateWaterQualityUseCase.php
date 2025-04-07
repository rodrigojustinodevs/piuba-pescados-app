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

            $analysisDate = $waterQuality->analysis_date instanceof Carbon
                ? $waterQuality->analysis_date
                : Carbon::parse($waterQuality->analysis_date);

            return new WaterQualityDTO(
                id: $waterQuality->id,
                tank: [
                    'id'   => $waterQuality->tank->id ?? '',
                    'name' => $waterQuality->tank->name ?? '',
                ],
                analysisDate: $analysisDate->toDateString(),
                ph: (float) $waterQuality->ph,
                oxygen: (float) $waterQuality->oxygen,
                temperature: (float) $waterQuality->temperature,
                ammonia: (float) $waterQuality->ammonia,
                createdAt: $waterQuality->created_at?->toDateTimeString(),
                updatedAt: $waterQuality->updated_at?->toDateTimeString()
            );
        });
    }
}
