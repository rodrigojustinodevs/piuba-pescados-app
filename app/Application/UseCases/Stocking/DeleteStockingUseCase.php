<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteStockingUseCase
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->stockingRepository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->stockingRepository->delete($id);
        });
    }
}
