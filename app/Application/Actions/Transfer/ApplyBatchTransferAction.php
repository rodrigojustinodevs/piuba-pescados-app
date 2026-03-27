<?php

declare(strict_types=1);

namespace App\Application\Actions\Transfer;

use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class ApplyBatchTransferAction
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    /**
     * Move o lote para o tanque de destino e desconta a quantidade transferida.
     * Chamada após a persistência da transferência, dentro da transação do UseCase.
     */
    public function execute(
        string $batchId,
        string $destinationTankId,
        int $transferredQuantity,
        int $currentQuantity,
    ): void {
        $this->batchRepository->update($batchId, [
            'tank_id'          => $destinationTankId,
            'initial_quantity' => $currentQuantity - $transferredQuantity,
        ]);
    }

    /**
     * Reverte o lote ao tanque de origem e devolve a quantidade.
     * Chamada ao desfazer (delete) uma transferência, dentro da transação do UseCase.
     */
    public function revert(
        string $batchId,
        string $originTankId,
        int $transferredQuantity,
        int $currentQuantity,
    ): void {
        $this->batchRepository->update($batchId, [
            'tank_id'          => $originTankId,
            'initial_quantity' => $currentQuantity + $transferredQuantity,
        ]);
    }
}
