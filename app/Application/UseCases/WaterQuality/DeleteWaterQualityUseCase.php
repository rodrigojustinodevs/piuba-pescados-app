<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteWaterQualityUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->waterQualityRepository->delete($id));
    }
}
