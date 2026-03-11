<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\SurvivalRateDTO;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Services\Batch\BatchPerformanceService;
use RuntimeException;

class SurvivalRateUseCase
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BatchPerformanceService $performanceService
    ) {
    }

    public function execute(string $batchId): SurvivalRateDTO
    {
        $batch = $this->batchRepository->showBatch('id', $batchId);

        if (! $batch instanceof Batch) {
            throw new RuntimeException('Batch not found');
        }

        $survivalRate     = $this->performanceService->calculateSurvivalRate($batch);
        $currentSurvivors = $this->performanceService->getCurrentPopulation($batch);
        $totalMortalities = $batch->initial_quantity - $currentSurvivors;

        return new SurvivalRateDTO(
            batchId: (string) $batch->id,
            initialQuantity: (int) $batch->initial_quantity,
            totalMortalities: (int) $totalMortalities,
            currentSurvivors: $currentSurvivors,
            survivalRate: $survivalRate
        );
    }
}
