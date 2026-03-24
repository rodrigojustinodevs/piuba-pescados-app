<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Exceptions\InsufficientBiomassException;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\Log;

final readonly class GuardBiomassAction
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    /**
     * Valida biomassa disponível sem margem de tolerância.
     * Usada em fluxos simples de venda sem despesca total.
     *
     * @throws InsufficientBiomassException
     */
    public function execute(
        Stocking $stocking,
        float $requestedWeight,
        ?string $excludeSaleId = null,
    ): void {
        $available = $stocking->initialBiomass()
            - $this->saleRepository->soldWeightByStocking($stocking->id, $excludeSaleId);

        if ($requestedWeight > $available) {
            throw new InsufficientBiomassException(
                available:  $available,
                requested:  $requestedWeight,
                stockingId: $stocking->id,
            );
        }
    }

    /**
     * Valida biomassa com margem de tolerância configurável.
     * Usada em despescas onde há variação natural de peso.
     *
     * Acima da estimativa mas dentro da tolerância → warning (não bloqueia).
     * Acima da tolerância → InsufficientBiomassException.
     *
     * @throws InsufficientBiomassException
     */
    public function executeWithTolerance(
        Stocking $stocking,
        float $requestedWeight,
        float $tolerancePercent,
        ?string $excludeSaleId = null,
    ): void {
        $committedWeight = $this->saleRepository->soldWeightByStocking($stocking->id, $excludeSaleId);
        $available       = $stocking->initialBiomass() - $committedWeight;
        $toleranceLimit  = $available * (1 + $tolerancePercent / 100);

        if ($requestedWeight > $toleranceLimit) {
            throw new InsufficientBiomassException(
                available:  $toleranceLimit,
                requested:  $requestedWeight,
                stockingId: $stocking->id,
            );
        }

        if ($requestedWeight > $available) {
            Log::warning('Harvest adjustment: requested weight exceeds biomass estimate but is within tolerance.', [
                'stocking_id'       => $stocking->id,
                'available_kg'      => $available,
                'requested_kg'      => $requestedWeight,
                'tolerance_percent' => $tolerancePercent,
                'tolerance_limit'   => $toleranceLimit,
                'excess_kg'         => round($requestedWeight - $available, 4),
            ]);
        }
    }
}
