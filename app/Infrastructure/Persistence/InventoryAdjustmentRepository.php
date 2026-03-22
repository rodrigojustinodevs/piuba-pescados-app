<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Enums\InventoryAdjustmentStatus;
use App\Domain\Models\InventoryAdjustment;
use App\Domain\Repositories\InventoryAdjustmentRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class InventoryAdjustmentRepository implements InventoryAdjustmentRepositoryInterface
{
    public function create(array $attributes): InventoryAdjustment
    {
        /** @var InventoryAdjustment */
        return InventoryAdjustment::create($attributes);
    }

    public function linkTransaction(
        InventoryAdjustment $adjustment,
        string $transactionId,
    ): InventoryAdjustment {
        $adjustment->update(['reference_transaction_id' => $transactionId]);

        return $adjustment->refresh();
    }

    public function markAsApplied(InventoryAdjustment $adjustment): InventoryAdjustment
    {
        $adjustment->update(['status' => InventoryAdjustmentStatus::APPLIED->value]);

        return $adjustment->refresh();
    }

    public function paginate(array $filters): PaginationInterface
    {
        $paginator = InventoryAdjustment::with(['stock.supply', 'user'])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['stock_id']),
                static fn ($q) => $q->where('stock_id', $filters['stock_id']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    InventoryAdjustmentStatus::from($filters['status'])->value,
                ),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}