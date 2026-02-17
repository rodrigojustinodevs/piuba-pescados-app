<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Mappers\TransferMapper;
use RuntimeException;

class ShowTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository
    ) {
    }

    public function execute(string $id): ?TransferDTO
    {
        $transfer = $this->transferRepository->showTransfer('id', $id);

        if (! $transfer instanceof \App\Domain\Models\Transfer) {
            throw new RuntimeException('Transfer not found');
        }

        return TransferMapper::toDTO($transfer);
    }
}
