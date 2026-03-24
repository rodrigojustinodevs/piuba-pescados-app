<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Services\SaleService;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private SaleService $saleService,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(string $id, array $data): Sale
    {
        $sale = $this->repository->findOrFail($id);

        $attributes = $this->buildAttributes($sale, $data);

        return DB::transaction(
            fn (): Sale => $this->repository->update($id, $attributes)
        );
    }

    /**
     * Merges the current sale state with the incoming patch, re-validating biomass
     * when total_weight changes. Fields absent from $data are not overwritten.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildAttributes(Sale $sale, array $data): array
    {
        $attributes = [];

        if (array_key_exists('client_id', $data)) {
            $attributes['client_id'] = $data['client_id'];
        }

        if (array_key_exists('total_weight', $data)) {
            $newWeight  = (float) $data['total_weight'];
            $stockingId = $data['stocking_id'] ?? $sale->stocking_id;

            // Re-validate biomass excluding the current sale's own committed weight
            if ($stockingId !== null) {
                $this->saleService->guardBiomass(
                    stockingId:      (string) $stockingId,
                    requestedWeight: $newWeight,
                    excludeSaleId:   $sale->id,
                );
            }

            $attributes['total_weight'] = $newWeight;
        }

        if (array_key_exists('price_per_kg', $data)) {
            $attributes['price_per_kg'] = (float) $data['price_per_kg'];
        }

        if (isset($attributes['total_weight']) || isset($attributes['price_per_kg'])) {
            $weight                      = $attributes['total_weight'] ?? (float) $sale->total_weight;
            $price                       = $attributes['price_per_kg'] ?? (float) $sale->price_per_kg;
            $attributes['total_revenue'] = round($weight * $price, 2);
        }

        if (array_key_exists('sale_date', $data)) {
            $attributes['sale_date'] = $data['sale_date'];
        }

        if (array_key_exists('status', $data)) {
            $attributes['status'] = SaleStatus::from((string) $data['status'])->value;
        }

        if (array_key_exists('notes', $data)) {
            $attributes['notes'] = $data['notes'];
        }

        return $attributes;
    }
}
