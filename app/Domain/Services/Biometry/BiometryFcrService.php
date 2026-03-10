<?php

declare(strict_types=1);

namespace App\Domain\Services\Biometry;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;

class BiometryFcrService
{
    public function __construct(
        private readonly FeedingRepositoryInterface $feedingRepository,
        private readonly BiometryRepositoryInterface $biometryRepository,
        private readonly MortalityRepositoryInterface $mortalityRepository,
    ) {
    }

    public function calculate(Batch $batch, float $currentWeight, string $biometryDate): float
    {
        // 1. Busca os dados do período (Última biometria ou início do lote)
        $previousBiometry = $this->biometryRepository->findLatestBeforeDate($batch->id, $biometryDate);

        $previousWeight = $previousBiometry instanceof \App\Domain\Models\Biometry
            ? (float) $previousBiometry->average_weight
            : 0.0;
        $startDate = $previousBiometry instanceof \App\Domain\Models\Biometry
            ? $previousBiometry->biometry_date
            : $batch->entry_date;

        // 2. Ração consumida APENAS neste período
        $startDateStr = $startDate instanceof \DateTimeInterface
            ? $startDate->format('Y-m-d')
            : (string) $startDate;

        $feedInPeriod = $this->feedingRepository->totalFeedConsumedUntilDate(
            $batch->id,
            $startDateStr,
            $biometryDate
        );

        if ($feedInPeriod <= 0.0) {
            return 0.0;
        }

        // 3. Ganho de biomassa no período
        $biomassGainKg = $this->biomassGainKg($batch, $currentWeight, $previousWeight);

        if ($biomassGainKg <= 0.0) {
            return 0.0;
        }

        // 4. Retorna o FCR do período
        return round($feedInPeriod / $biomassGainKg, 4);
    }

    /**
     * Calcula o ganho de biomassa em Quilos (KG)
     */
    private function biomassGainKg(Batch $batch, float $currentWeight, float $previousWeight): float
    {
        $livingQuantity = $this->estimatedLivingQuantity($batch);

        $individualGain = $currentWeight - $previousWeight;

        // Se o peixe perdeu peso (ou medição errada), não há ganho de biomassa
        if ($individualGain <= 0.0) {
            return 0.0;
        }

        return ($individualGain * $livingQuantity) / 1000;
    }

    /**
     * Calcula a quantidade real de peixes vivos no tanque
     */
    private function estimatedLivingQuantity(Batch $batch): int
    {
        $totalMortalities = $this->mortalityRepository->totalMortalities($batch->id);

        // Subtrai as mortes da quantidade inicial (nunca retornando menos que 0)
        return max(0, $batch->initial_quantity - $totalMortalities);
    }

    /**
     * Calcula average_weight a partir de sample_weight/sample_quantity.
     */
    public function calculateAverageWeight(float $sampleWeight, int $sampleQuantity, float $averageWeight = 0.0): float
    {
        if ($sampleWeight > 0 && $sampleQuantity > 0) {
            return round($sampleWeight / $sampleQuantity, 4);
        }

        return $averageWeight;
    }
}
