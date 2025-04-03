<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Domain\Repositories\SettlementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSettlementUseCase
{
    public function __construct(
        protected SettlementRepositoryInterface $settlementRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->settlementRepository->delete($id));
    }
}
