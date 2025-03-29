<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Repositories\StockRepositoryInterface;

class ShowStockUseCase
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {
    }

    public function execute(string $id): ?StockDTO
    {
        $stock = $this->stockRepository->showStock('id', $id);

        if (! $stock instanceof \App\Domain\Models\Stock) {
            return null;
        }

        return new StockDTO(
            id: $stock->id,
            supplyName: $stock->supply_name,
            currentQuantity: $stock->current_quantity,
            unit: $stock->unit,
            minimumStock: $stock->minimum_stock,
            withdrawnQuantity: $stock->withdrawn_quantity,
            company: [
                'name' => $stock->company->name ?? '',
            ],
            createdAt: $stock->created_at?->toDateTimeString(),
            updatedAt: $stock->updated_at?->toDateTimeString()
        );
    }
}
