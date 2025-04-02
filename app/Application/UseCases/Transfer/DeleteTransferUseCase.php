<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Repositories\TransferRepositoryInterface;

class DeleteTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->transferRepository->delete($id);
    }
}
