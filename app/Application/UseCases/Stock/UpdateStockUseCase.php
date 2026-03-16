<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Infrastructure\Mappers\StockMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateStockUseCase
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): StockDTO
    {
        return DB::transaction(function () use ($id, $data): StockDTO {
            $stock      = $this->stockRepository->showStock('id', $id);
            $mappedData = StockMapper::fromRequest($data);

            if (! $stock instanceof Stock) {
                throw new RuntimeException('Stock not found');
            }

            $updatedStock = $this->stockRepository->update($id, $mappedData);

            if (! $updatedStock instanceof Stock) {
                throw new RuntimeException('Stock not found');
            }

            return StockMapper::toDTO($updatedStock);
        });
    }
}
