<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Application\DTOs\BatchDistributionInputDTO;

final readonly class ValidateTanksForDistributionAction
{
    public function __construct(
        private ValidateActiveBatchInTankAction $validateTank,
    ) {
    }

    /**
     * @throws \App\Domain\Exceptions\TankAlreadyHasActiveBatchException
     */
    public function execute(BatchDistributionInputDTO $input): void
    {
        foreach ($input->distribution as $item) {
            $this->validateTank->execute($item['tankId']);
        }
    }
}
