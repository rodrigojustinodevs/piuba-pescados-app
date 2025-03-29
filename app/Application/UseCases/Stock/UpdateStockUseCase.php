<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateStockUseCase
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public function execute(string $id, array $data): StockDTO
    {
        return DB::transaction(function () use ($id, $data): StockDTO {
            $stock = $this->stockRepository->update($id, $data);

            if (! $stock instanceof Stock) {
                throw new Exception('Stock not found');
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
        });
    }
}
