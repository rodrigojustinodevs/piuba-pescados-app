<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateStockUseCase
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): StockDTO
    {
        return DB::transaction(function () use ($data): StockDTO {
            $stock = $this->stockRepository->create($data);

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
        });
    }
}
