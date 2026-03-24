<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\CostAllocationInputDTO;
use App\Domain\Models\CostAllocation;

interface CostAllocationRepositoryInterface
{
    /**
     * Persist the cost allocation header and all its distribution items atomically.
     *
     * @param array<int, array{stocking_id: string, percentage: float, amount: float}> $items
     */
    public function createWithItems(
        CostAllocationInputDTO $dto,
        float $totalAmount,
        array $items,
    ): CostAllocation;

    /**
     * Find by ID or throw ModelNotFoundException.
     * Eager-loads company, financialTransaction, and items.stocking.
     */
    public function findOrFail(string $id): CostAllocation;

    /**
     * Soft-delete the allocation (items cascade via DB FK).
     */
    public function delete(string $id): void;

    /**
     * Paginate allocations filtered by company.
     *
     * @param array{
     *     company_id: string,
     *     financial_transaction_id?: string|null,
     *     allocation_method?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;
}
