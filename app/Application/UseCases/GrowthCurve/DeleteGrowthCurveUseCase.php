<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteGrowthCurveUseCase
{
    public function __construct(
        protected GrowthCurveRepositoryInterface $growthCurveRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->growthCurveRepository->delete($id));
    }
}
