<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\BatchDistributionInputDTO;
use App\Application\Actions\FinancialTransaction\GeneratePayableAction;
use App\Application\DTOs\ExpenseInputDTO;
use App\Domain\Enums\FinancialTransactionStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class CreateDistributedBatchesAction
{
    public function __construct(
        private CompanyResolverInterface $companyResolver,
        private CreateSingleDistributedBatchAction $createSingleBatch,
        private GeneratePayableAction $generatePayable,
    ) {}

    /**
     * @return Collection<int, \App\Domain\Models\Batch>
     */
    public function execute(BatchDistributionInputDTO $input): Collection
    {
        $parentGroupId = Str::uuid()->toString();
        $totalQuantity = $input->getTotalQuantity();
        $companyId = $this->companyResolver->resolve();
        $expenseInputDTO = new ExpenseInputDTO(
            companyId: $companyId,
            amount: $input->totalCost,
            expenseDate: $input->entryDate,
            financialCategoryId: null,
            supplierId: $input->supplierId,
            costCenterId: null,
            description: null,
            notes: $input->notes,
            status: FinancialTransactionStatus::PENDING,
            paymentDate: null,
        );
        
        $this->generatePayable->execute($expenseInputDTO, $parentGroupId);
        return $this->createBatchesForDistribution(
            $input,
            $parentGroupId,
            $totalQuantity,
        );
    }

    /**
     * @return Collection<int, \App\Domain\Models\Batch>
     */
    private function createBatchesForDistribution(
        BatchDistributionInputDTO $input,
        string $parentGroupId,
        int $totalQuantity,
    ): Collection {
        $createdBatches = collect();

        foreach ($input->distribution as $item) {
            $batch = $this->createSingleBatch->execute(
                $input,
                $item,
                $parentGroupId,
                $totalQuantity
            );

            $createdBatches->push($batch);
        }

        return $createdBatches;
    }
}