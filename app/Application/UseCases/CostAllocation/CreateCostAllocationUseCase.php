<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\CostAllocationInputDTO;
use App\Application\Services\CostAllocationService;
use App\Domain\Models\CostAllocation;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateCostAllocationUseCase
{
    public function __construct(
        private CostAllocationRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
        private CostAllocationService $allocationService,
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(array $data): CostAllocation
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = CostAllocationInputDTO::fromArray($data);

        return DB::transaction(function () use ($dto): CostAllocation {
            $transaction = $this->allocationService->guardTransaction(
                $dto->financialTransactionId,
            );

            $stockings = $this->allocationService->guardStockings($dto->stockingIds);

            $totalAmount = (float) $transaction->amount;

            $items = $this->allocationService->computeAmounts(
                method:      $dto->allocationMethod,
                totalAmount: $totalAmount,
                stockings:   $stockings,
            );

            $allocation = $this->repository->createWithItems($dto, $totalAmount, $items);

            // ── Step 5: Mark the transaction as allocated (prevent duplicates) ─
            $transaction->update(['is_allocated' => true]);

            // ── Step 6: Increment accumulated_fixed_cost on all stockings — 1 query ──
            $this->stockingRepository->bulkIncrementFixedCost(
                array_column($items, 'amount', 'stocking_id'),
            );

            return $allocation;
        });
    }
}
