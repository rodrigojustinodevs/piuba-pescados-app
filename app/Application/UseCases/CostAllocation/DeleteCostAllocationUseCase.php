<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Domain\Repositories\CostAllocationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteCostAllocationUseCase
{
    public function __construct(
        protected CostAllocationRepositoryInterface $costAllocationRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->costAllocationRepository->delete($id));
    }
}
