<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\CostAllocationDTO;
use App\Application\UseCases\CostAllocation\CreateCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\DeleteCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\ListCostAllocationsUseCase;
use App\Application\UseCases\CostAllocation\ShowCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\UpdateCostAllocationUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CostAllocationService
{
    public function __construct(
        protected CreateCostAllocationUseCase $createCostAllocationUseCase,
        protected ListCostAllocationsUseCase $listCostAllocationsUseCase,
        protected ShowCostAllocationUseCase $showCostAllocationUseCase,
        protected UpdateCostAllocationUseCase $updateCostAllocationUseCase,
        protected DeleteCostAllocationUseCase $deleteCostAllocationUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): CostAllocationDTO
    {
        return $this->createCostAllocationUseCase->execute($data);
    }

    public function showAllCostAllocations(): AnonymousResourceCollection
    {
        return $this->listCostAllocationsUseCase->execute();
    }

    public function showCostAllocation(string $id): ?CostAllocationDTO
    {
        return $this->showCostAllocationUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCostAllocation(string $id, array $data): CostAllocationDTO
    {
        return $this->updateCostAllocationUseCase->execute($id, $data);
    }

    public function deleteCostAllocation(string $id): bool
    {
        return $this->deleteCostAllocationUseCase->execute($id);
    }
}
