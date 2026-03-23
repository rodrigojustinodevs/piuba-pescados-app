<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\WaterQualityRepositoryInterface;

class UpdateWaterQualityUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): WaterQuality
    {
        $quality = $this->waterQualityRepository->update($id, $data);

        return $quality->load('tank');
    }
}
