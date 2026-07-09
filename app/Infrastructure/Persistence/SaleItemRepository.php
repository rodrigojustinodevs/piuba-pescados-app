<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SaleItemDTO;
use App\Domain\Models\SaleItem;
use App\Domain\Repositories\SaleItemRepositoryInterface;
use Illuminate\Support\Collection;

final class SaleItemRepository implements SaleItemRepositoryInterface
{
    public function create(string $saleId, SaleItemDTO $dto): SaleItem
    {
        /** @var SaleItem $item */
        $item = SaleItem::create([
            'sale_id'          => $saleId,
            'batch_id'         => $dto->batchId,
            'stocking_id'      => $dto->stockingId,
            'product_name'     => $dto->productName,
            'species'          => $dto->species,
            'category'         => $dto->category,
            'total_weight'     => $dto->totalWeight,
            'price_per_kg'     => $dto->pricePerKg,
            'subtotal'         => $dto->subtotal(),
            'unit_cost'        => 0,
            'total_cost'       => 0,
            'is_total_harvest' => $dto->isHarvestTotal,
            'notes'            => $dto->notes,
        ]);

        return $item;
    }

    public function findBySale(string $saleId): Collection
    {
        return SaleItem::where('sale_id', $saleId)->get();
    }

    public function updateCosts(string $saleItemId, float $unitCost, float $totalCost): void
    {
        SaleItem::whereKey($saleItemId)->update([
            'unit_cost'  => $unitCost,
            'total_cost' => $totalCost,
        ]);
    }

    public function syncFirstItemWeightAndPrice(string $saleId, float $totalWeight, float $pricePerKg): void
    {
        $item = SaleItem::where('sale_id', $saleId)->oldest()->first();

        if ($item === null) {
            return;
        }

        $item->update([
            'total_weight' => $totalWeight,
            'price_per_kg' => $pricePerKg,
            'subtotal'     => round($totalWeight * $pricePerKg, 2),
        ]);
    }
}
