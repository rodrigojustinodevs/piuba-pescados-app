<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\DTOs\SaleDTO;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateSaleUseCase
{
    public function __construct(
        protected SaleRepositoryInterface $saleRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): SaleDTO
    {
        return DB::transaction(function () use ($id, $data): SaleDTO {
            $sale = $this->saleRepository->update($id, $data);

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
        });
    }
}
