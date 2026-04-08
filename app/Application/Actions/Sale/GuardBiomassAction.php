<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Exceptions\InsufficientBiomassException;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\Log;

final readonly class GuardBiomassAction
{
    /**
     * Tolerância de segurança padrão: 50% acima da biomassa estimada.
     * Cobre variações naturais de peso (ciclo lunar, jejum pré-despesca, etc.).
     */
    private const float DEFAULT_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    /**
     * Valida biomassa disponível sem margem de tolerância.
     * Usada no Update (revalidação simples sem flag de despesca).
     *
     * @throws InsufficientBiomassException
     */
    public function execute(
        Stocking $stocking,
        float $requestedWeight,
        ?string $excludeSaleId = null,
    ): void {
        [$available] = $this->resolveAvailability($stocking, $excludeSaleId);

        if ($requestedWeight > $available) {
            throw new InsufficientBiomassException(
                available:  $available,
                requested:  $requestedWeight,
                stockingId: (string) $stocking->id,
            );
        }
    }

    /**
     * Valida biomassa com tolerância de 50% (regra 2 da despesca).
     *
     * Fórmula da biomassa disponível:
     *   (current_quantity × avg_weight) − soma_de_peso_já_vendido_deste_stocking
     *
     * Limite com tolerância:
     *   disponível × (1 + tolerancePercent / 100)
     *
     * Fluxo:
     *  - requestedWeight ≤ disponível          → OK silencioso
     *  - disponível < requestedWeight ≤ limite  → warning (log) + OK
     *  - requestedWeight > limite               → InsufficientBiomassException + rollback
     *
     * @throws InsufficientBiomassException
     */
    public function executeWithTolerance(
        Stocking $stocking,
        float $requestedWeight,
        float $tolerancePercent = self::DEFAULT_TOLERANCE_PERCENT,
        ?string $excludeSaleId = null,
    ): void {
        [$available, $committedWeight] = $this->resolveAvailability($stocking, $excludeSaleId);

        $toleranceLimit = $available * (1 + $tolerancePercent / 100);

        if ($requestedWeight > $toleranceLimit) {
            throw new InsufficientBiomassException(
                available:  $toleranceLimit,
                requested:  $requestedWeight,
                stockingId: (string) $stocking->id,
            );
        }

        if ($requestedWeight > $available) {
            Log::warning('Harvest adjustment: weight exceeds biomass estimate but within tolerance.', [
                'stocking_id'       => $stocking->id,
                'current_quantity'  => $stocking->current_quantity,
                'avg_weight_kg'     => $stocking->average_weight,
                'committed_kg'      => $committedWeight,
                'available_kg'      => $available,
                'requested_kg'      => $requestedWeight,
                'tolerance_percent' => $tolerancePercent,
                'tolerance_limit'   => $toleranceLimit,
                'excess_kg'         => round($requestedWeight - $available, 4),
            ]);
        }
    }

    /**
     * Calcula a biomassa disponível do povoamento.
     *
     * Regra 2 (fórmula exata):
     *   disponível = (current_quantity × average_weight) − peso_já_vendido_do_stocking
     *
     * Retorna [disponível, pesoComprometido] para evitar recalcular.
     *
     * @return array{float, float}
     */
    private function resolveAvailability(Stocking $stocking, ?string $excludeSaleId): array
    {
        // Biomassa atual estimada do povoamento
        $currentBiomass = (float) $stocking->current_quantity
                        * (float) $stocking->average_weight;

        // Peso já comprometido em vendas anteriores deste MESMO stocking_id
        $committedWeight = $this->saleRepository->soldWeightByStocking(
            (string) $stocking->id,
            $excludeSaleId,
        );

        $available = $currentBiomass - $committedWeight;

        return [$available, $committedWeight];
    }
}
