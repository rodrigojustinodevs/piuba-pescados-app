<?php

declare(strict_types=1);

namespace App\Domain\Services\Alert;

use App\Domain\Models\Batch;
use App\Domain\Repositories\AlertRepositoryInterface;

class AlertService
{
    private const FCR_ALERT_THRESHOLD = 2.0;

    private const DENSITY_ALERT_THRESHOLD = 50.0;

    /** Desvio percentual (0.20 = 20%) entre ração fornecida e recomendada para gerar alerta */
    private const RATION_DEVIATION_THRESHOLD = 0.20;

    public function __construct(
        private readonly AlertRepositoryInterface $alertRepository,
    ) {}

    public function checkHighFcr(Batch $batch, float $fcr): void
    {
        if ($fcr <= self::FCR_ALERT_THRESHOLD) {
            return;
        }

        $this->alertRepository->createHighFcrAlert(
            $batch,
            $fcr,
            self::FCR_ALERT_THRESHOLD
        );
    }

    public function checkDensityAlert(Batch $batch, float $density): void
    {
        if ($density <= self::DENSITY_ALERT_THRESHOLD) {
            return;
        }

        $this->alertRepository->createDensityAlert(
            $batch,
            $density,
            self::DENSITY_ALERT_THRESHOLD
        );
    }

    /**
     * Compara quantidade fornecida com a ração recomendada da última biometria.
     * Cria alerta se o desvio percentual for maior que RATION_DEVIATION_THRESHOLD.
     */
    public function checkRationDeviation(
        Batch $batch,
        float $quantityProvided,
        ?float $recommendedRation
    ): void {
        if ($recommendedRation === null || $recommendedRation <= 0.0) {
            return;
        }

        $deviation = abs($quantityProvided - $recommendedRation) / $recommendedRation;
        if ($deviation <= self::RATION_DEVIATION_THRESHOLD) {
            return;
        }

        $this->alertRepository->createRationDeviationAlert(
            $batch,
            $quantityProvided,
            $recommendedRation
        );
    }
}
