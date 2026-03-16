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

        $measuredAt = $quality->measured_at instanceof Carbon
            ? $quality->measured_at
            : Carbon::parse($quality->measured_at);

        return new WaterQualityDTO(
            id: $quality->id,
            ph: $quality->ph,
            dissolvedOxygen: $quality->dissolved_oxygen,
            temperature: $quality->temperature,
            ammonia: $quality->ammonia,
            tank: [
                'id'   => $quality->tank->id ?? '',
                'name' => $quality->tank->name ?? '',
            ],
            measuredAt: $measuredAt->toDateString(),
            createdAt: $quality->created_at?->toDateTimeString(),
            updatedAt: $quality->updated_at?->toDateTimeString()
        );
    }
}
