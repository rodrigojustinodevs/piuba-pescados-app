<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class UpdateWaterQualityUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): WaterQualityDTO
    {
        $quality = $this->waterQualityRepository->update($id, $data);

        if (! $quality instanceof WaterQuality) {
            throw new RuntimeException('Water quality record not found');
        }

        $analysisDate = $quality->analysis_date instanceof Carbon
            ? $quality->analysis_date
            : Carbon::parse($quality->analysis_date);

        return new WaterQualityDTO(
            id: $quality->id,
            analysisDate: $analysisDate->toDateString(),
            ph: $quality->ph,
            oxygen: $quality->oxygen,
            temperature: $quality->temperature,
            ammonia: $quality->ammonia,
            tank: [
                'id'   => $quality->tank->id ?? '',
                'name' => $quality->tank->name ?? '',
            ],
            createdAt: $quality->created_at?->toDateTimeString(),
            updatedAt: $quality->updated_at?->toDateTimeString()
        );
    }
}
