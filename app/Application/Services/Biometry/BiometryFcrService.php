<?php

declare(strict_types=1);

namespace App\Application\Services\Biometry;

use App\Domain\Models\Batch;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;
use DateTimeInterface;

final readonly class BiometryFcrService
{
    public function __construct(
        private FeedingRepositoryInterface $feedingRepository,
        private BiometryRepositoryInterface $biometryRepository,
        private MortalityRepositoryInterface $mortalityRepository,
    ) {
    }

    public function calculate(Batch $batch, float $currentWeight, string $biometryDate): float
    {
        $previousBiometry = $this->biometryRepository->findLatestBeforeDate($batch->id, $biometryDate);

        $previousWeight = $previousBiometry instanceof Biometry
            ? (float) $previousBiometry->average_weight
            : 0.0;

        $startDate = $previousBiometry instanceof Biometry
            ? $previousBiometry->biometry_date
            : $batch->entry_date;

        $startDateStr = $startDate instanceof DateTimeInterface
            ? $startDate->format('Y-m-d')
            : (string) $startDate;

        $feedInPeriod = $this->feedingRepository->totalFeedConsumedUntilDate(
            $batch->id,
            $startDateStr,
            $biometryDate,
        );

        if ($feedInPeriod <= 0.0) {
            return 0.0;
        }

        $biomassGainKg = $this->biomassGainKg($batch, $currentWeight, $previousWeight);

        if ($biomassGainKg <= 0.0) {
            return 0.0;
        }

        return round($feedInPeriod / $biomassGainKg, 4);
    }

    public function calculateAverageWeight(float $sampleWeight, int $sampleQuantity, float $averageWeight = 0.0): float
    {
        if ($sampleWeight > 0 && $sampleQuantity > 0) {
            return round($sampleWeight / $sampleQuantity, 4);
        }

        return $averageWeight;
    }

    private function biomassGainKg(Batch $batch, float $currentWeight, float $previousWeight): float
    {
        $livingQuantity = $this->estimatedLivingQuantity($batch);
        $individualGain = $currentWeight - $previousWeight;

        if ($individualGain <= 0.0) {
            return 0.0;
        }

        return ($individualGain * $livingQuantity) / 1000;
    }

    private function estimatedLivingQuantity(Batch $batch): int
    {
        $totalMortalities = $this->mortalityRepository->totalMortalities($batch->id);

        return max(0, $batch->initial_quantity - $totalMortalities);
    }
}
