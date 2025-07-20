<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Application\DTOs\GrowthCurveDTO;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateGrowthCurveUseCase
{
    public function __construct(
        protected GrowthCurveRepositoryInterface $growthCurveRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): GrowthCurveDTO
    {
        return DB::transaction(function () use ($data): GrowthCurveDTO {
            $growthCurve = $this->growthCurveRepository->create($data);
        });
    }
}
