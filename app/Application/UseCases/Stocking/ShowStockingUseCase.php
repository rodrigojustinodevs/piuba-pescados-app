<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingDTO;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Infrastructure\Mappers\StockingMapper;
use RuntimeException;

class ShowStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    public function execute(string $id): ?StockingDTO
    {
        $stocking = $this->stockingRepository->showStocking('id', $id);

        if (! $stocking instanceof Stocking) {
            throw new RuntimeException('Stocking not found');
        }

        return StockingMapper::toDTO($stocking);
    }
}
