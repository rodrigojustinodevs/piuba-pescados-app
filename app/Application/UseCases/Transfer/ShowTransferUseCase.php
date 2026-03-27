<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Models\Transfer;
use App\Domain\Repositories\TransferRepositoryInterface;

final readonly class ShowTransferUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
    ) {
    }

    public function execute(string $id): Transfer
    {
        return $this->transferRepository->findOrFail($id);
    }
}
