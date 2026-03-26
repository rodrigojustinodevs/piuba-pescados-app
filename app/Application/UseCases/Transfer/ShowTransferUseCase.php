<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Models\Transfer;
use App\Domain\Repositories\TransferRepositoryInterface;
use RuntimeException;

class ShowTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository
    ) {
    }

    public function execute(string $id): Transfer
    {
        $transfer = $this->transferRepository->showTransfer('id', $id);

        if (! $transfer instanceof Transfer) {
            throw new RuntimeException('Transfer not found');
        }

        return $transfer;
    }
}
