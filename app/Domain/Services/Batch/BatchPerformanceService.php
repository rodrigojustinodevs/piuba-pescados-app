<?php

declare(strict_types=1);

namespace App\Domain\Services\Batch;

use App\Domain\Models\Batch;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use RuntimeException;

class BatchPerformanceService
{
    public function __construct(
        private MortalityRepositoryInterface $mortalityRepository,
        private FeedingRepositoryInterface $feedingRepository,
        private BiometryRepositoryInterface $biometryRepository
    ) {}
    /**
     * Calculate the survival rate.
     * @param Batch $batch The batch to calculate the survival rate.
     * @return float The survival rate.
     */
    public function calculateSurvivalRate(Batch $batch): float
    {
        if ($batch->initial_quantity <= 0) {
            return 0.0;
        }

        $survivors = $this->getCurrentPopulation($batch);
        
        $rate = ($survivors / $batch->initial_quantity) * 100;

        return round(max(0, $rate), 2);
    }

    /**
     * Get the current population of the batch.
     * @param Batch $batch The batch to get the current population.
     * @return int The current population.
     */
    public function getCurrentPopulation(Batch $batch): int
    {
        $totalMortalities = $this->mortalityRepository->totalMortalities($batch->id);
        
        return max(0, $batch->initial_quantity - $totalMortalities);
    }

    /**
     * Calculate the estimated financial loss based on mortality.
     * @param float $feedPrice Average price of the feed per KG
     * @param float $fingerlingPrice Price per unit of fingerling/juvenile
     */
    public function calculateFinancialLoss(Batch $batch, float $feedPrice, float $fingerlingPrice): float
    {
        $totalMortalities = $this->mortalityRepository->totalMortalities($batch->id);
        
        if ($totalMortalities <= 0) return 0.0;

        $totalFeed = $this->feedingRepository->totalFeedConsumedUntilDate(
            $batch->id, 
            $batch->entry_date->format('Y-m-d'), 
            now()->format('Y-m-d')
        );

        $feedCostPerFish = ($totalFeed * $feedPrice) / $batch->initial_quantity;

        $loss = ($fingerlingPrice + $feedCostPerFish) * $totalMortalities;

        return round($loss, 2);
    }

    public function calculateCurrentBiomass(Batch $batch): float
    {
        $survivors = $this->getCurrentPopulation($batch);
        $latestBiometry = $this->biometryRepository->findLatestByBatch($batch->id);
        $latestWeight = $latestBiometry?->average_weight ?? 0.0;

        return ($survivors * $latestWeight) / 1000; // Retorna em KG
    }

    /** 
     * Calculate the total financial cost invested in feed for the batch.
     * * @param Batch $batch
     * @param float $currentFeedPrice Current price of the feed per KG
     * @return float
     */
    public function calculateTotalInvestedInFeed(Batch $batch, float $currentFeedPrice): float
    {
        $totalFeedConsumed = $this->feedingRepository->getTotalFeedConsumedByBatch($batch->id);

        $totalCost = $totalFeedConsumed * $currentFeedPrice;

        return round($totalCost, 2);    
    }

    public function buildPerformance(
        Batch $batch,
        ?Biometry $biometry,
        float $feedPrice
    ): array {
    
        if (!$biometry) {
            throw new RuntimeException('Biometry not found');
        }
    
        if ($feedPrice <= 0) {
            throw new RuntimeException('Feed price not found');
        }
    
        return [
            'identity' => [
                'name' => $batch->name,
                'species' => $batch->species,
                'daysInCultivation' => $batch->entry_date->diffInDays(now()),
            ],
            'biologicalPerformance' => [
                'survivalRate' => $this->calculateSurvivalRate($batch),
                'currentPopulation' => $this->getCurrentPopulation($batch),
                'averageWeight' => $biometry->average_weight,
                'currentFcr' => $biometry->fcr,
                'biomassEstimated' => $biometry->biomass_estimated,
            ],
            'financialPerformance' => [
                'estimated_loss' => $this->calculateFinancialLoss(
                    $batch,
                    $feedPrice,
                    $batch->unit_cost
                ),
                'totalFeedCost' => $this->calculateTotalInvestedInFeed(
                    $batch,
                    $feedPrice
                ),
            ],
        ];
    }
}