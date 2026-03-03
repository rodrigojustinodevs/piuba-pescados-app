<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->stockingRepository->delete($id));
    }
}
