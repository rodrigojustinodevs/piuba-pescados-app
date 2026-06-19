<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Actions\Transfer\ApplyBatchTransferAction;
use App\Application\Actions\Transfer\GuardTransferRulesAction;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateTransferUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private BatchRepositoryInterface $batchRepository,
        private GuardTransferRulesAction $guardRules,
        private ApplyBatchTransferAction $applyBatchTransfer,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest (snake_case)
     */
    public function execute(string $id, array $data): Transfer
    {
        $current = $this->transferRepository->findOrFail($id);

        $effectiveOrigin = (string) ($data['origin_tank_id'] ?? $current->origin_tank_id);
        $effectiveDest   = (string) ($data['destination_tank_id'] ?? $current->destination_tank_id);

        $destinationChanged = array_key_exists('destination_tank_id', $data)
            && $effectiveDest !== (string) $current->destination_tank_id;

        $batchChanged = array_key_exists('batch_id', $data);

        $currentStatus   = (string) $current->status;
        $newStatus       = (string) ($data['status'] ?? $currentStatus);
        $wasCompleted    = $currentStatus === 'completed';
        $willBeCompleted = $newStatus === 'completed';

        // M-06 + M-07: precisa reverter se estava completed e algo de movimento mudou
        $shouldRevert = $wasCompleted && ($destinationChanged || $batchChanged || ! $willBeCompleted);

        // Precisa aplicar se ficará completed e algo de movimento mudou (ou não estava aplicado antes)
        $shouldApply = $willBeCompleted && ($destinationChanged || $batchChanged || ! $wasCompleted);

        // Valida regras de negócio fora da transação
        $this->guardRules->guardUpdate(
            $effectiveOrigin,
            $effectiveDest,
            (string) $current->batch_id,
            $destinationChanged,
        );

        return DB::transaction(function () use ($id, $data, $current, $shouldRevert, $shouldApply): Transfer {
            // M-06: reverte o estado anterior antes de reaplicar com os novos dados
            if ($shouldRevert) {
                $batchToRevert = $this->batchRepository->findOrFail((string) $current->batch_id);
                $this->applyBatchTransfer->revert(
                    $batchToRevert,
                    (string) $current->origin_tank_id,
                    (int) $current->quantity,
                    $current->child_batch_id,
                );
                // Limpa o child_batch_id pois o sub-lote foi removido na reversão
                $data['child_batch_id'] = null;
            }

            $attributes = array_filter($data, static fn ($v): bool => $v !== null);
            $transfer   = $this->transferRepository->update($id, $attributes);

            if ($shouldApply) {
                $batch        = $this->batchRepository->findOrFail((string) $transfer->batch_id);
                $childBatchId = $this->applyBatchTransfer->execute(
                    $batch,
                    (string) $transfer->destination_tank_id,
                    (int) $transfer->quantity,
                );

                if ($childBatchId !== null) {
                    $transfer = $this->transferRepository->update($id, ['child_batch_id' => $childBatchId]);
                }
            }

            return $transfer;
        });
    }
}
