<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\CostAllocationInputDTO;
use App\Domain\Enums\AllocationMethod;
use App\Domain\Models\CostAllocation;
use App\Domain\Models\CostAllocationItem;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class CostAllocationRepository implements CostAllocationRepositoryInterface
{
    /**
     * @param array<int, array{stocking_id: string, percentage: float, amount: float}> $items
     */
    public function createWithItems(
        CostAllocationInputDTO $dto,
        float $totalAmount,
        array $items,
    ): CostAllocation {
        /** @var CostAllocation $allocation */
        $allocation = CostAllocation::create([
            'company_id'               => $dto->companyId,
            'financial_transaction_id' => $dto->financialTransactionId,
            'allocation_method'        => $dto->allocationMethod->value,
            'total_amount'             => $totalAmount,
            'notes'                    => $dto->notes,
        ]);

        foreach ($items as $item) {
            CostAllocationItem::create([
                'cost_allocation_id' => $allocation->id,
                'stocking_id'        => $item['stocking_id'],
                'percentage'         => $item['percentage'],
                'amount'             => $item['amount'],
            ]);
        }

        return $allocation->load([
            'company:id,name',
            'financialTransaction',
            'items.stocking',
        ]);
    }

    public function findOrFail(string $id): CostAllocation
    {
        return CostAllocation::with([
            'company:id,name',
            'financialTransaction',
            'items.stocking',
        ])->findOrFail($id);
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }

    /**
     * @param array{
     *     company_id: string,
     *     financial_transaction_id?: string|null,
     *     allocation_method?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = CostAllocation::with([
            'company:id,name',
            'financialTransaction',
            'items.stocking',
        ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['financial_transaction_id']),
                static fn ($q) => $q->where(
                    'financial_transaction_id',
                    $filters['financial_transaction_id'],
                ),
            )
            ->when(
                ! empty($filters['allocation_method']),
                static fn ($q) => $q->where(
                    'allocation_method',
                    AllocationMethod::from((string) $filters['allocation_method'])->value,
                ),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}
