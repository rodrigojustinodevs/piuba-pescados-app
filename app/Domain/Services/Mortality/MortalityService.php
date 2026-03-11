<?php

declare(strict_types=1);

namespace App\Domain\Services\Mortality;

use App\Domain\Models\Batch;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Services\Alert\AlertService;

class MortalityService
{
    private const float CRITICAL_MORTALITY_RATE_THRESHOLD = 10.0;

    public function __construct(
        private readonly MortalityRepositoryInterface $mortalityRepository,
        private readonly AlertService $alertService
    ) {
    }

    public function checkAndDispatchIfCritical(Batch $batch): void
    {
        $total = $this->mortalityRepository->totalMortalities($batch->id);

        if ($batch->initial_quantity <= 0) {
            return;
        }

        $mortalityRate = ($total / $batch->initial_quantity) * 100;

        if ($mortalityRate > self::CRITICAL_MORTALITY_RATE_THRESHOLD) {
            $this->alertService->checkHighMortality($batch, $mortalityRate);
        }
    }
}
