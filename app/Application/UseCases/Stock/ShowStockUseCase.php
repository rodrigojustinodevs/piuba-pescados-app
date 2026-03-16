<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockDTO;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Infrastructure\Mappers\StockMapper;
use RuntimeException;

class ShowStockUseCase
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {
    }

    public function execute(string $id): ?StockDTO
    {
        $stock = $this->stockRepository->showStock('id', $id);

        if (! $stock instanceof Stock) {
            throw new RuntimeException('Stock not found');
        }

        return StockMapper::toDTO($stock);
    }
}
