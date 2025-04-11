<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Application\DTOs\CostAllocationDTO;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateCostAllocationUseCase
{
    public function __construct(
        protected CostAllocationRepositoryInterface $costAllocationRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): CostAllocationDTO
    {
        return DB::transaction(function () use ($data): CostAllocationDTO {
            $costAllocation = $this->costAllocationRepository->create($data);

            return new CostAllocationDTO(
                id: $costAllocation->id,
                description: $costAllocation->description,
                amount: (float) $costAllocation->amount, //Falta devolver os decimal
                registrationDate: $costAllocation->registration_date,
                company: [
                    'name' => $costAllocation->company->name ?? '',
                ],
                createdAt: $costAllocation->created_at?->toDateTimeString(),
                updatedAt: $costAllocation->updated_at?->toDateTimeString()
            );
        });
    }
}
