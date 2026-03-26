<?php

declare(strict_types=1);

namespace App\Application\Services\Batch;

use App\Domain\Models\Batch;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;

final readonly class BatchPerformanceService
{
    public function __construct(
        private MortalityRepositoryInterface $mortalityRepository,
        private FeedingRepositoryInterface $feedingRepository,
        private BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    public function calculateSurvivalRate(Batch $batch): float
    {
        if ($batch->initial_quantity <= 0) {
            return 0.0;
        }

        $survivors = $this->getCurrentPopulation($batch);
        $rate      = ($survivors / $batch->initial_quantity) * 100;

        return round(max(0, $rate), 2);
    }

    public function getCurrentPopulation(Batch $batch): int
    {
        $totalMortalities = $this->mortalityRepository->totalMortalities((string) $batch->id);

        return max(0, $batch->initial_quantity - $totalMortalities);
    }

    public function calculateFinancialLoss(Batch $batch, float $feedPrice, float $fingerlingPrice): float
    {
        $totalMortalities = $this->mortalityRepository->totalMortalities((string) $batch->id);

        if ($totalMortalities <= 0) {
            return 0.0;
        }

        $totalFeed = $this->feedingRepository->totalFeedConsumedUntilDate(
            (string) $batch->id,
            $batch->entry_date->format('Y-m-d'),
            now()->format('Y-m-d'),
        );

        $feedCostPerFish = ($totalFeed * $feedPrice) / $batch->initial_quantity;
        $loss            = ($fingerlingPrice + $feedCostPerFish) * $totalMortalities;

        return round($loss, 2);
    }

    public function calculateCurrentBiomass(Batch $batch): float
    {
        $survivors      = $this->getCurrentPopulation($batch);
        $latestBiometry = $this->biometryRepository->findLatestByBatch((string) $batch->id);
        $latestWeight   = $latestBiometry->average_weight ?? 0.0;

        return ($survivors * $latestWeight) / 1000;
    }

    public function calculateTotalInvestedInFeed(Batch $batch, float $currentFeedPrice): float
    {
        $totalFeedConsumed = $this->feedingRepository->getTotalFeedConsumedByBatch((string) $batch->id);

        return round($totalFeedConsumed * $currentFeedPrice, 2);
    }

    /**
     * @return array<string, array<string, float|int|string|null>>
     */
    public function buildPerformance(Batch $batch, ?Biometry $biometry, float $feedPrice): array
    {
        if (! $biometry instanceof Biometry) {
            throw new \RuntimeException('Biometry not found');
        }

        if ($feedPrice <= 0) {
            throw new \RuntimeException('Feed price not found');
        }

        return [
            'identity' => [
                'name'              => $batch->name,
                'species'           => $batch->species,
                'daysInCultivation' => $batch->entry_date->diffInDays(now()),
            ],
            'biologicalPerformance' => [
                'survivalRate'      => $this->calculateSurvivalRate($batch),
                'currentPopulation' => $this->getCurrentPopulation($batch),
                'averageWeight'     => $biometry->average_weight,
                'currentFcr'        => $biometry->fcr,
                'biomassEstimated'  => $biometry->biomass_estimated,
            ],
            'financialPerformance' => [
                'estimated_loss' => $this->calculateFinancialLoss(
                    $batch,
                    $feedPrice,
                    (float) $batch->unit_cost,
                ),
                'totalFeedCost' => $this->calculateTotalInvestedInFeed(
                    $batch,
                    $feedPrice,
                ),
            ],
        ];
    }
}
