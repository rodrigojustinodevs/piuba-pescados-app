<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Services\Stock\StockService;
use App\Infrastructure\Mappers\StockMapper;
use Illuminate\Support\Facades\DB;

class CreateStockUseCase
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): StockDTO
    {
        return DB::transaction(function () use ($data): StockDTO {
            $mappedData = StockMapper::fromRequest($data);
            
            $stock = $this->stockService->addEntry(
                companyId: $mappedData['company_id'],
                quantity: (float) $mappedData['current_quantity'],
                totalCost: (float) ($mappedData['total_cost'] ?? 0),
                unitPrice: (float) ($mappedData['unit_price'] ?? 0),
                unit: $mappedData['unit'] ?? 'kg',
                minimumStock: (float) ($mappedData['minimum_stock'] ?? 0),
                supplierId: $mappedData['supplier_id'] ?? null
            );

            return StockMapper::toDTO($stock);
        });
    }
}
