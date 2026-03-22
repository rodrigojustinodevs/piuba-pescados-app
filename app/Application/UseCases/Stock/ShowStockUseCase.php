<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;

final readonly class ShowStockUseCase
{
    public function __construct(
        private StockRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Stock
    {
        return $this->repository->findOrFail($id);
    }
}
