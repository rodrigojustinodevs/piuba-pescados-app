<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use RuntimeException;

class ShowStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    public function execute(string $id): Stocking
    {
        $stocking = $this->stockingRepository->showStocking('id', $id);

        if (! $stocking instanceof Stocking) {
            throw new RuntimeException('Stocking not found');
        }

        return $stocking;
    }
}
