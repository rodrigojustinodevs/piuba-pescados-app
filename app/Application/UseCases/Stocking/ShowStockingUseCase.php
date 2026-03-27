<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;

final readonly class ShowStockingUseCase
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    public function execute(string $id): Stocking
    {
        return $this->stockingRepository->findOrFail($id);
    }
}
