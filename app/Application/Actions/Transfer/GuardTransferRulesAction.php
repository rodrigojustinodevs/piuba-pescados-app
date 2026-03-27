<?php

declare(strict_types=1);

namespace App\Application\Actions\Transfer;

use App\Domain\Exceptions\TankAlreadyHasActiveBatchException;
use App\Domain\Exceptions\TransferBatchOriginMismatchException;
use App\Domain\Exceptions\TransferSameTankException;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class GuardTransferRulesAction
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    /**
     * Valida as regras de negócio para criação de transferência:
     *  1. O lote deve estar no tanque de origem informado.
     *  2. O tanque de destino não pode ter outro lote ativo.
     *
     * @throws TransferBatchOriginMismatchException
     * @throws TankAlreadyHasActiveBatchException
     */
    public function guardCreate(
        Batch $batch,
        string $originTankId,
        string $destinationTankId,
    ): void {
        if ((string) $batch->tank_id !== $originTankId) {
            throw new TransferBatchOriginMismatchException();
        }

        if ($this->batchRepository->hasActiveBatchInTank($destinationTankId, $batch->id)) {
            throw new TankAlreadyHasActiveBatchException($destinationTankId);
        }
    }

    /**
     * Valida as regras de negócio para atualização de transferência:
     *  1. Origem e destino não podem ser o mesmo tanque.
     *  2. Se o destino mudou, o novo destino não pode ter outro lote ativo.
     *
     * @throws TransferSameTankException
     * @throws TankAlreadyHasActiveBatchException
     */
    public function guardUpdate(
        string $effectiveOrigin,
        string $effectiveDest,
        ?string $batchId,
        bool $destinationChanged,
    ): void {
        if ($effectiveOrigin === $effectiveDest) {
            throw new TransferSameTankException();
        }

        if (
            $destinationChanged
            && $batchId !== null
            && $this->batchRepository->hasActiveBatchInTank($effectiveDest, $batchId)
        ) {
            throw new TankAlreadyHasActiveBatchException($effectiveDest);
        }
    }
}
