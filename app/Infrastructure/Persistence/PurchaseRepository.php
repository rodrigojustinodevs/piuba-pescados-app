<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\PurchaseDTO;
use App\Application\DTOs\PurchaseItemDTO;
use App\Domain\Enums\PurchaseStatus;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PurchaseRepositoryInterface;

final class PurchaseRepository implements PurchaseRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'supplier:id,name',
        'company:id,name',
        'items.supply:id,name,unit',
    ];

    public function create(PurchaseDTO $dto): Purchase
    {
        /** @var Purchase $purchase */
        $purchase = Purchase::create([
            'code'           => $dto->code,
            'company_id'     => $dto->companyId,
            'supplier_id'    => $dto->supplierId,
            'order_date'     => $dto->orderDate,
            'expected_date'  => $dto->expectedDate,
            'invoice_number' => $dto->invoiceNumber,
            'status'         => $dto->status->value,
            'payment_status' => $dto->paymentStatus->value,
            'payment_method' => $dto->paymentMethod?->value,
            'total_price'    => $dto->totalPrice(),
            'freight'        => $dto->freight,
            'other_costs'    => $dto->otherCosts,
            'notes'          => $dto->notes,
            'responsible'    => $dto->responsible,
            'received_date'  => $dto->receivedDate,
        ]);

        foreach ($dto->items as $item) {
            $purchase->items()->create($item->toPersistence());
        }

        return $purchase->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
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
     *     companyId: string,
     *     status?: string|null,
     *     paymentStatus?: string|null,
     *     paymentMethod?: string|null,
     *     supplierId?: string|null,
     *     code?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Purchase::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', PurchaseStatus::from($filters['status'])->value),
            )
            ->when(
                ! empty($filters['paymentStatus']),
                static fn ($q) => $q->where('payment_status', $filters['paymentStatus']),
            )
            ->when(
                ! empty($filters['paymentMethod']),
                static fn ($q) => $q->where('payment_method', $filters['paymentMethod']),
            )
            ->when(
                ! empty($filters['supplierId']),
                static fn ($q) => $q->where('supplier_id', $filters['supplierId']),
            )
            ->when(
                ! empty($filters['code']),
                static fn ($q) => $q->where('code', 'like', '%' . $filters['code'] . '%'),
            )
            ->when(
                ! empty($filters['dateFrom']),
                static fn ($q) => $q->whereDate('order_date', '>=', $filters['dateFrom']),
            )
            ->when(
                ! empty($filters['dateTo']),
                static fn ($q) => $q->whereDate('order_date', '<=', $filters['dateTo']),
            )
            ->latest('order_date')
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function showPurchase(string $field, string | int $value): ?Purchase
    {
        return Purchase::with(self::DEFAULT_RELATIONS)
            ->where($field, $value)
            ->first();
    }

    public function findOrFail(string $id): Purchase
    {
        return Purchase::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
