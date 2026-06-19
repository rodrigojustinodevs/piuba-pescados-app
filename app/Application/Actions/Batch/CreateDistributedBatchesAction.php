<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Application\Actions\FinancialTransaction\GeneratePayableAction;
use App\Application\DTOs\BatchDistributionInputDTO;
use App\Application\DTOs\ExpenseInputDTO;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\DistributionCrossCompanyException;
use App\Domain\Models\Tank;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class CreateDistributedBatchesAction
{
    public function __construct(
        private CreateSingleDistributedBatchAction $createSingleBatch,
        private GeneratePayableAction $generatePayable,
        private TankRepositoryInterface $tankRepository,
        private FinancialCategoryRepositoryInterface $financialCategoryRepository,
    ) {
    }

    /**
     * @return Collection<int, \App\Domain\Models\Batch>
     */
    public function execute(BatchDistributionInputDTO $input): Collection
    {
        $parentGroupId = Str::uuid()->toString();
        $totalQuantity = $input->getTotalQuantity();

        // Passo 1: valida TODOS os tanques antes de qualquer escrita.
        // Se um tanque nao existir ou pertencer a outra empresa, falha aqui.
        $companyId           = $this->resolveAndValidateCompany($input);
        $financialCategoryId = $this->financialCategoryRepository
            ->showFinancialCategory('code', FinancialType::PURCHASE->value)->id;

        $expenseInputDTO = new ExpenseInputDTO(
            companyId:           $companyId,
            amount:              $input->totalCost,
            expenseDate:         $input->entryDate,
            financialCategoryId: $financialCategoryId,
            supplierId:          $input->supplierId,
            costCenterId:        null,
            description:         null,
            notes:               $input->notes,
            status:              FinancialTransactionStatus::PENDING,
        );

        $this->generatePayable->execute($expenseInputDTO, $parentGroupId);

        return $this->createBatchesForDistribution($input, $parentGroupId, $totalQuantity);
    }

    /**
     * Carrega todos os tanques da distribuicao, verifica que todos pertencem
     * a mesma empresa e retorna o company_id unico.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Se algum tanque nao existir
     * @throws DistributionCrossCompanyException Se os tanques forem de empresas distintas
     */
    private function resolveAndValidateCompany(BatchDistributionInputDTO $input): string
    {
        $tankIds = array_column($input->distribution, 'tankId');

        if ($tankIds === []) {
            throw new \InvalidArgumentException('A distribuicao deve ter ao menos um item.');
        }

        // Carrega todos os tanques de uma vez — sem N queries dentro do loop
        $tanks = collect($tankIds)->map(
            fn (string $tankId): Tank => $this->tankRepository->findOrFail($tankId)
        );

        // Coleta os company_ids unicos de todos os tanques
        $distinctCompanyIds = $tanks
            ->pluck('company_id')
            ->map(static fn ($id): string => (string) $id)
            ->unique()
            ->values()
            ->all();

        // Mais de uma empresa = distribuicao cross-company invalida
        if (count($distinctCompanyIds) > 1) {
            throw new DistributionCrossCompanyException($distinctCompanyIds);
        }

        return $distinctCompanyIds[0];
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
                $totalQuantity,
            );

            $createdBatches->push($batch);
        }

        return $createdBatches;
    }
}
