<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\DTOs\SaleDTO;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class ShowSaleUseCase
{
    public function __construct(
        protected SaleRepositoryInterface $saleRepository
    ) {
    }

    public function execute(string $id): ?SaleDTO
    {
        $sale = $this->saleRepository->showSale('id', $id);

        if (! $sale instanceof Sale) {
            throw new RuntimeException('Sale not found');
        }

        $saleDate = $sale->sale_date instanceof Carbon
            ? $sale->sale_date
            : Carbon::parse($sale->sale_date);

        return new SaleDTO(
            id: $sale->id,
            totalWeight: $sale->total_weight,
            pricePerKg: $sale->price_per_kg,
            totalRevenue: $sale->total_revenue,
            saleDate: $saleDate->toDateString(),
            company: [
                'name' => $sale->company->name ?? null,
            ],
            client: [
                'id'   => $sale->client->id ?? null,
                'name' => $sale->client->name ?? null,
            ],
            batcheId: $sale->batche_id,
            createdAt: $sale->created_at?->toDateTimeString(),
            updatedAt: $sale->updated_at?->toDateTimeString()
        );
    }
}
