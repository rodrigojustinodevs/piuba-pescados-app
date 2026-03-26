<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\SurvivalRateDTO;
use App\Application\Services\Batch\BatchPerformanceService;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class SurvivalRateUseCase
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
        private BatchPerformanceService $performanceService,
    ) {
    }

    public function execute(string $batchId): SurvivalRateDTO
    {
        $batch = $this->batchRepository->showBatch('id', $batchId);

        if (! $batch instanceof Batch) {
            throw new \RuntimeException('Batch not found');
        }

        $survivalRate     = $this->performanceService->calculateSurvivalRate($batch);
        $currentSurvivors = $this->performanceService->getCurrentPopulation($batch);
        $totalMortalities = $batch->initial_quantity - $currentSurvivors;

        return new SurvivalRateDTO(
            batchId:          (string) $batch->id,
            initialQuantity:  (int) $batch->initial_quantity,
            totalMortalities: $totalMortalities,
            currentSurvivors: $currentSurvivors,
            survivalRate:     $survivalRate,
        );
    }
}
