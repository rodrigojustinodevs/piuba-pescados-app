<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Application\DTOs\CostAllocationDTO;
use App\Domain\Models\CostAllocation;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use RuntimeException;

class ShowCostAllocationUseCase
{
    public function __construct(
        protected CostAllocationRepositoryInterface $costAllocationRepository
    ) {}

    public function execute(string $id): ?CostAllocationDTO
    {
        $costAllocation = $this->costAllocationRepository->showCostAllocation('id', $id);

        if (! $costAllocation instanceof CostAllocation) {
            throw new RuntimeException('Cost Allocation not found');
        }

        return new CostAllocationDTO(
            id: $costAllocation->id,
            description: $costAllocation->description,
            amount: (float) $costAllocation->amount,
            registrationDate: $costAllocation->registration_date,
            company: [
                'name' => $costAllocation->company->name ?? '',
            ],
            createdAt: $costAllocation->created_at?->toDateTimeString(),
            updatedAt: $costAllocation->updated_at?->toDateTimeString()
        );
    }
}
