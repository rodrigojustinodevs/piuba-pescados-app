<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    public function execute(string $id): bool
    {
        DB::beginTransaction();

        try {
            $deleted = $this->stockingRepository->delete($id);

            DB::commit();

            return $deleted;
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
