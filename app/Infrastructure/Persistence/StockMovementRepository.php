<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\RegisterStockMovementDTO;
use App\Domain\Models\StockMovement;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockMovementRepositoryInterface;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    public function create(RegisterStockMovementDTO $dto): StockMovement
    {
        /** @var StockMovement */
        return StockMovement::create([
            'stock_id'  => $dto->stockId,
            'supply_id' => $dto->supplyId,
            'user_id'   => $dto->userId,
            'type'      => $dto->type->value,
            'quantity'  => $dto->quantity,
            'reason'    => $dto->reason,
        ])->load(['supply:id,name,unit', 'stock:id,name,code']);
    }

    /** @param array<string, mixed> $filters */
    public function paginateByStock(string $stockId, array $filters): PaginationInterface
    {
        $paginator = StockMovement::with(['supply:id,name,unit', 'stock:id,name,code'])
            ->where('stock_id', $stockId)
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where('type', $filters['type']),
            )
            ->when(
                ! empty($filters['supplyId']),
                static fn ($q) => $q->where('supply_id', $filters['supplyId']),
            )
            ->latest('created_at')
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}
