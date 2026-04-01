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

        // Valida regras de negócio fora da transação
        $this->guardRules->guardUpdate(
            effectiveOrigin:    $effectiveOrigin,
            effectiveDest:      $effectiveDest,
            batchId:            (string) $current->batch_id,
            destinationChanged: $destinationChanged,
        );

        return DB::transaction(function () use ($id, $data, $destinationChanged): Transfer {
            // Persiste apenas os campos presentes no patch — sem array manual de mapeamento
            $attributes = array_filter($data, static fn ($v): bool => $v !== null);

            $transfer = $this->transferRepository->update($id, $attributes);

            // Atualiza o lote apenas quando tanque destino ou lote mudaram
            if ($destinationChanged || array_key_exists('batch_id', $data)) {
                $batch = $this->batchRepository->findOrFail((string) $transfer->batch_id);

                $this->applyBatchTransfer->execute(
                    batchId:             (string) $transfer->batch_id,
                    destinationTankId:   (string) $transfer->destination_tank_id,
                    transferredQuantity: (int) $transfer->quantity,
                    currentQuantity:     (int) $batch->initial_quantity,
                );
            }

            return $transfer;
        });
    }
}
