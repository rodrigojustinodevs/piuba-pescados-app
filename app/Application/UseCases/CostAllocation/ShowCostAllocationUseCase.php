<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Domain\Models\CostAllocation;
use App\Domain\Repositories\CostAllocationRepositoryInterface;

final readonly class ShowCostAllocationUseCase
{
    public function __construct(
        private CostAllocationRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): CostAllocation
    {
        return $this->repository->findOrFail($id);
    }
}
