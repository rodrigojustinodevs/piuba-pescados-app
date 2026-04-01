<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Application\DTOs\GrowthCurveInputDTO;
use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateGrowthCurveUseCase
{
    public function __construct(
        private GrowthCurveRepositoryInterface $growthCurveRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): GrowthCurve
    {
        return DB::transaction(function () use ($data): GrowthCurve {
            $dto = GrowthCurveInputDTO::fromArray($data);

            return $this->growthCurveRepository->create($dto);
        });
    }
}
