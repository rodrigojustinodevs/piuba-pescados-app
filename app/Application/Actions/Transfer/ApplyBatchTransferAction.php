<?php

declare(strict_types=1);

namespace App\Application\Actions\Transfer;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class ApplyBatchTransferAction
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    /**
     * Move o lote para o destino e desconta a quantidade transferida.
     *
     * Transferência TOTAL (quantity == initial_quantity):
     *   - O lote original é movido para o destino.
     *   - Retorna null (nenhum sub-lote criado).
     *
     * Transferência PARCIAL (quantity < initial_quantity):
     *   - O lote original permanece na origem com a quantidade reduzida.
     *   - Um sub-lote filho é criado no destino com a quantidade transferida.
     *   - Retorna o ID do sub-lote criado.
     */
    public function execute(
        Batch $batch,
        string $destinationTankId,
        int $transferredQuantity,
    ): ?string {
        $isPartial = $transferredQuantity < $batch->initial_quantity;

        if ($isPartial) {
            $this->batchRepository->update((string) $batch->id, [
                'initial_quantity' => $batch->initial_quantity - $transferredQuantity,
            ]);

            $child = $this->batchRepository->createChildBatch($batch, $destinationTankId, $transferredQuantity);

            return (string) $child->id;
        }

        // Transferência total: move o lote original
        $this->batchRepository->update((string) $batch->id, [
            'tank_id'          => $destinationTankId,
            'initial_quantity' => $batch->initial_quantity - $transferredQuantity,
        ]);

        return null;
    }

    /**
     * Reverte o efeito de uma transferência.
     *
     * Se havia sub-lote (parcial): exclui o filho e restaura a quantidade do original.
     * Se não havia sub-lote (total): move o original de volta à origem e restaura a quantidade.
     */
    public function revert(
        Batch $batch,
        string $originTankId,
        int $transferredQuantity,
        ?string $childBatchId,
    ): void {
        if ($childBatchId !== null) {
            $this->batchRepository->delete($childBatchId);

            $this->batchRepository->update((string) $batch->id, [
                'initial_quantity' => $batch->initial_quantity + $transferredQuantity,
            ]);

            return;
        }

        $this->batchRepository->update((string) $batch->id, [
            'tank_id'          => $originTankId,
            'initial_quantity' => $batch->initial_quantity + $transferredQuantity,
        ]);
    }
}
