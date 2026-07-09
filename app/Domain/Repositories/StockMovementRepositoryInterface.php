<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\RegisterStockMovementDTO;
use App\Domain\Models\StockMovement;

interface StockMovementRepositoryInterface
{
    public function create(RegisterStockMovementDTO $dto): StockMovement;

    /** @param array<string, mixed> $filters */
    public function paginateByStock(string $stockId, array $filters): PaginationInterface;
}
