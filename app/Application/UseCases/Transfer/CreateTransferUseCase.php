<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Actions\Transfer\ApplyBatchTransferAction;
use App\Application\Actions\Transfer\GuardTransferRulesAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\TransferInputDTO;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateTransferUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private BatchRepositoryInterface $batchRepository,
        private CompanyResolverInterface $companyResolver,
        private GuardTransferRulesAction $guardRules,
        private ApplyBatchTransferAction $applyBatchTransfer,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(array $data): Transfer
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto   = TransferInputDTO::fromArray($data);
        $batch = $this->batchRepository->findOrFail($dto->batchId);

        // Valida regras de negócio fora da transação — sem custo de lock
        $this->guardRules->guardCreate(
            batch:             $batch,
            originTankId:      $dto->originTankId,
            destinationTankId: $dto->destinationTankId,
        );

        return DB::transaction(function () use ($dto, $batch): Transfer {
            $transfer = $this->transferRepository->create($dto);

            $this->applyBatchTransfer->execute(
                batchId:              $dto->batchId,
                destinationTankId:    $dto->destinationTankId,
                transferredQuantity:  $dto->quantity,
                currentQuantity:      (int) $batch->initial_quantity,
            );

            return $transfer;
        });
    }
}
