<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Domain\Exceptions\TankAlreadyHasActiveBatchException;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class ValidateActiveBatchInTankAction
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    public function execute(string $tankId, ?string $exceptBatchId = null): void
    {
        if ($this->batchRepository->hasActiveBatchInTank($tankId, $exceptBatchId)) {
            throw new TankAlreadyHasActiveBatchException($tankId);
        }
    }
}
