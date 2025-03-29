<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Repositories\StockRepositoryInterface;

class DeleteStockUseCase
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->stockRepository->delete($id);
    }
}
