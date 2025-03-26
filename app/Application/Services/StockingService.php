<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\StockingDTO;
use App\Application\UseCases\Stocking\CreateStockingUseCase;
use App\Application\UseCases\Stocking\DeleteStockingUseCase;
use App\Application\UseCases\Stocking\ListStockingsUseCase;
use App\Application\UseCases\Stocking\ShowStockingUseCase;
use App\Application\UseCases\Stocking\UpdateStockingUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockingService
{
    public function __construct(
        protected CreateStockingUseCase $createStockingUseCase,
        protected ListStockingsUseCase $listStockingsUseCase,
        protected ShowStockingUseCase $showStockingUseCase,
        protected UpdateStockingUseCase $updateStockingUseCase,
        protected DeleteStockingUseCase $deleteStockingUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): StockingDTO
    {
        return $this->createStockingUseCase->execute($data);
    }

    public function showAllStockings(): AnonymousResourceCollection
    {
        return $this->listStockingsUseCase->execute();
    }

    public function showStocking(string $id): ?StockingDTO
    {
        return $this->showStockingUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateStocking(string $id, array $data): StockingDTO
    {
        return $this->updateStockingUseCase->execute($id, $data);
    }

    public function deleteStocking(string $id): bool
    {
        return $this->deleteStockingUseCase->execute($id);
    }
}
