<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\StockDTO;
use App\Application\UseCases\Stock\CreateStockUseCase;
use App\Application\UseCases\Stock\DeleteStockUseCase;
use App\Application\UseCases\Stock\ListStocksUseCase;
use App\Application\UseCases\Stock\ShowStockUseCase;
use App\Application\UseCases\Stock\UpdateStockUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockService
{
    public function __construct(
        protected CreateStockUseCase $createStockUseCase,
        protected ListStocksUseCase $listStocksUseCase,
        protected ShowStockUseCase $showStockUseCase,
        protected UpdateStockUseCase $updateStockUseCase,
        protected DeleteStockUseCase $deleteStockUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): StockDTO
    {
        return $this->createStockUseCase->execute($data);
    }

    public function showAllStocks(): AnonymousResourceCollection
    {
        return $this->listStocksUseCase->execute();
    }

    public function showStock(string $id): ?StockDTO
    {
        return $this->showStockUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateStock(string $id, array $data): StockDTO
    {
        return $this->updateStockUseCase->execute($id, $data);
    }

    public function deleteStock(string $id): bool
    {
        return $this->deleteStockUseCase->execute($id);
    }
}
