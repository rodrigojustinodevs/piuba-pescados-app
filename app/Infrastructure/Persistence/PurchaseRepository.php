<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Purchase;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Application\DTOs\PurchaseDTO;
use App\Application\DTOs\PurchaseItemDTO;
use App\Domain\Enums\PurchaseStatus;

final class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function create(PurchaseDTO $dto): Purchase
    {
        /** @var Purchase $purchase */
        $purchase = Purchase::create([
            'company_id'     => $dto->companyId,
            'supplier_id'    => $dto->supplierId,
            'purchase_date'  => $dto->purchaseDate,
            'invoice_number' => $dto->invoiceNumber,
            'status'         => $dto->status->value,
            'total_price'    => $dto->totalPrice(),
            'received_at'    => $dto->receivedAt,
        ]);
 
        foreach ($dto->items as $item) {
            $purchase->items()->create($item->toPersistence());
        }
 
        return $purchase->load('items');
    }

    
    public function update(string $id, array $attributes): Purchase
    {
        $purchase = $this->findOrFail($id);
 
        $purchase->update($attributes);
 
        return $purchase->refresh();
    }

    public function syncItems(Purchase $purchase, array $itemDTOs): void
    {
        $existing    = $purchase->items->keyBy('id');
        $incomingIds = collect($itemDTOs)
            ->map(static fn (PurchaseItemDTO $dto): ?string => $dto->id)
            ->filter()
            ->values();

        $existing
            ->reject(static fn ($item): bool => $incomingIds->contains($item->id))
            ->each(static fn ($item): bool => $item->delete());

        foreach ($itemDTOs as $dto) {
            if ($dto->id !== null && $existing->has($dto->id)) {
                $existing->get($dto->id)->update($dto->toPersistence());
                continue;
            }

            $purchase->items()->create($dto->toPersistence());
        }
    }

    /**
     * @param array{
     *     company_id: string,
     *     status?: string|null,
     *     supplier_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Purchase::with([
                'supplier:id,name',
                'company:id,name',
                'items.supply:id,name,default_unit',
            ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    PurchaseStatus::from($filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['supplier_id']),
                static fn ($q) => $q->where('supplier_id', $filters['supplier_id']),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('purchase_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('purchase_date', '<=', $filters['date_to']),
            )
            ->latest('purchase_date')
            ->paginate((int) ($filters['per_page'] ?? 25));
 
        return new PaginationPresentr($paginator);
    }

    public function showPurchase(string $field, string|int $value): ?Purchase
    {
        return Purchase::with([
            'supplier:id,name',
            'company:id,name',
            'items.supply:id,name,default_unit'
        ])
        ->where($field, $value)
        ->first();
    }

    public function findOrFail(string $id): Purchase
    {
        return Purchase::with([
            'supplier:id,name',
            'company:id,name',
            'items.supply:id,name,default_unit'
        ])->findOrFail($id);
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}