<?php

declare(strict_types=1);

namespace App\Application\Actions\Mortality;

use App\Domain\Models\Batch;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Services\Alert\AlertService;

final readonly class CheckCriticalMortalityAction
{
    private const float CRITICAL_MORTALITY_RATE_THRESHOLD = 10.0;

    public function __construct(
        private MortalityRepositoryInterface $mortalityRepository,
        private AlertService $alertService,
    ) {
    }

    public function execute(Batch $batch): void
    {
        if ($batch->initial_quantity <= 0) {
            return;
        }

        $total = $this->mortalityRepository->totalMortalities((string) $batch->id);

        $mortalityRate = ($total / $batch->initial_quantity) * 100;

        if ($mortalityRate > self::CRITICAL_MORTALITY_RATE_THRESHOLD) {
            $this->alertService->checkHighMortality($batch, $mortalityRate);
        }
    }
}
