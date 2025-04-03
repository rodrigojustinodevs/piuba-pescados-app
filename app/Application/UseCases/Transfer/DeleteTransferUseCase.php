<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->transferRepository->delete($id));
    }
}
