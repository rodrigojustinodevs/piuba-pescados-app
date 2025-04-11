<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Application\DTOs\CostAllocationDTO;
use App\Domain\Models\CostAllocation;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use RuntimeException;

class UpdateCostAllocationUseCase
{
    public function __construct(
        protected CostAllocationRepositoryInterface $costAllocationRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): CostAllocationDTO
    {
        $costAllocation = $this->costAllocationRepository->update($id, $data);

        if (! $costAllocation instanceof CostAllocation) {
            throw new RuntimeException('Cost Allocation not found');
        }

        return new CostAllocationDTO(
            id: $costAllocation->id,
            description: $costAllocation->description,
            amount: $costAllocation->amount,
            registrationDate: $costAllocation->registration_date,
            company: [
                'name' => $costAllocation->company->name ?? '',
            ],
            createdAt: $costAllocation->created_at?->toDateTimeString(),
            updatedAt: $costAllocation->updated_at?->toDateTimeString()
        );
    }
}
