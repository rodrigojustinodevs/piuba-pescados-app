<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Domain\Repositories\SettlementRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteSettlementUseCase
{
    public function __construct(
        protected SettlementRepositoryInterface $settlementRepository
    ) {
    }

    public function execute(string $id): bool
    {
        DB::beginTransaction();

        try {
            $deleted = $this->settlementRepository->delete($id);

            DB::commit();

            return $deleted;
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
