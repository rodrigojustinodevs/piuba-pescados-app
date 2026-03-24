<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Sale\GuardBiomassAction;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private GuardBiomassAction $guardBiomass,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
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
     * Mescla o estado atual da venda com o patch recebido.
     * Campos ausentes em $data não são sobrescritos.
     * Revalida biomassa quando total_weight muda.
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

            // Revalida biomassa excluindo o peso já comprometido por esta venda
            if ($stockingId !== null) {
                /** @var Stocking $stocking */
                $stocking = Stocking::findOrFail((string) $stockingId);

                $this->guardBiomass->execute(
                    stocking:        $stocking,
                    requestedWeight: $newWeight,
                    excludeSaleId:   $sale->id,
                );
            }

            $attributes['total_weight'] = $newWeight;
        }

        if (array_key_exists('price_per_kg', $data)) {
            $attributes['price_per_kg'] = (float) $data['price_per_kg'];
        }

        // Recalcula total_revenue se peso ou preço mudaram
        if (isset($attributes['total_weight']) || isset($attributes['price_per_kg'])) {
            $weight = $attributes['total_weight'] ?? (float) $sale->total_weight;
            $price  = $attributes['price_per_kg'] ?? (float) $sale->price_per_kg;

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
