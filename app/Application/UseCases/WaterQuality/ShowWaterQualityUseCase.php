<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use RuntimeException;

class ShowWaterQualityUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository
    ) {
    }

    public function execute(string $id): WaterQuality
    {
        $quality = $this->waterQualityRepository->showWaterQuality('id', $id);

        if (! $quality instanceof WaterQuality) {
            throw new RuntimeException('Water quality record not found');
        }

        return $quality->loadMissing('tank');
    }
}
