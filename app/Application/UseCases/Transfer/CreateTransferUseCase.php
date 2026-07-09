<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Actions\Transfer\ApplyBatchTransferAction;
use App\Application\Actions\Transfer\GuardTransferRulesAction;
use App\Application\DTOs\TransferInputDTO;
use App\Domain\Events\BatchTransferred;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use Illuminate\Support\Facades\DB;

final readonly class CreateTransferUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private BatchRepositoryInterface $batchRepository,
        private GuardTransferRulesAction $guardRules,
        private ApplyBatchTransferAction $applyBatchTransfer,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(array $data): Transfer
    {
        if (! CompanyContext::isMasterAdmin()) {
            $data['companyId'] = CompanyContext::requireCompanyId();
        }
        $dto   = TransferInputDTO::fromArray($data);
        $batch = $this->batchRepository->findOrFail($dto->batchId);

        // Valida regras de negócio fora da transação — sem custo de lock
        $this->guardRules->guardCreate(
            $batch,
            $dto->originTankId,
            $dto->destinationTankId,
            $dto->quantity,
        );

        $transfer = DB::transaction(function () use ($dto, $batch): Transfer {
            $transfer = $this->transferRepository->create($dto);

            // M-07: só move o lote quando a transferência está concluída
            if ($dto->status === 'completed') {
                $childBatchId = $this->applyBatchTransfer->execute(
                    $batch,
                    $dto->destinationTankId,
                    $dto->quantity,
                );

                if ($childBatchId !== null) {
                    $transfer = $this->transferRepository->update($transfer->id, [
                        'child_batch_id' => $childBatchId,
                    ]);
                }
            }

            return $transfer;
        });

        // M-04: ShouldDispatchAfterCommit — disparado após o commit da transação
        BatchTransferred::dispatch($transfer, $dto->companyId);

        return $transfer;
    }
}
