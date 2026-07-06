<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SaleInputDTO;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Models\SaleItem;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Collection;

final class SaleRepository implements SaleRepositoryInterface
{
    public function create(SaleInputDTO $dto): Sale
    {
        /** @var \App\Application\DTOs\SaleItemDTO $firstItem */
        $firstItem = $dto->firstItem();

        /** @var Sale $sale */
        $sale = Sale::create([
            'company_id'            => $dto->companyId,
            'client_id'             => $dto->clientId,
            // Colunas deprecated mantidas para compatibilidade — removidas na Fase 3
            'batch_id'              => $firstItem->batchId,
            'stocking_id'           => $firstItem->stockingId,
            'total_weight'          => array_sum(array_map(
                static fn ($i) => $i->totalWeight,
                $dto->items,
            )),
            'price_per_kg'          => $firstItem->pricePerKg,
            'is_total_harvest'      => $firstItem->isHarvestTotal,
            'financial_category_id' => $dto->financialCategoryId,
            'total_revenue'         => $dto->totalRevenue(),
            'sale_date'             => $dto->saleDate,
            'status'                => $dto->status->value,
            'notes'                 => $dto->notes,
            'payment_method'        => $dto->paymentMethod?->value,
            'invoice_number'        => $dto->invoiceNumber,
            'needs_invoice'         => $dto->needsInvoice,
            'discount'              => $dto->discount,
            'shipping'              => $dto->shipping,
            'taxes'                 => $dto->taxes,
            'due_date'              => $dto->dueDate,
            'paid_date'             => $dto->paidAt,
            'responsible_user_id'   => $dto->responsibleUserId,
        ]);

        foreach ($dto->items as $itemDto) {
            SaleItem::create([
                'sale_id'          => $sale->id,
                'batch_id'         => $itemDto->batchId,
                'stocking_id'      => $itemDto->stockingId,
                'product_name'     => $itemDto->productName,
                'species'          => $itemDto->species,
                'category'         => $itemDto->category,
                'total_weight'     => $itemDto->totalWeight,
                'price_per_kg'     => $itemDto->pricePerKg,
                'subtotal'         => $itemDto->subtotal(),
                'unit_cost'        => 0,
                'total_cost'       => 0,
                'is_total_harvest' => $itemDto->isHarvestTotal,
                'notes'            => $itemDto->notes,
            ]);
        }

        return $sale->load(['company:id,name', 'client:id,name', 'stocking', 'items.batch:id,name', 'items.stocking:id,quantity,average_weight']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sale
    {
        $sale = $this->findOrFail($id);

        $sale->update($attributes);

        return $sale->refresh()->load(['company:id,name', 'client:id,name', 'stocking', 'items.batch:id,name', 'items.stocking:id,quantity,average_weight']);
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findOrFail(string $id): Sale
    {
        return Sale::with([
            'company:id,name',
            'client:id,name',
            'stocking',
            'items.batch:id,name',
            'items.stocking:id,quantity,average_weight',
        ])->findOrFail($id);
    }

    /**
     * @param array{
     *     companyId: string,
     *     clientId?: string|null,
     *     batchId?: string|null,
     *     status?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Sale::with([
            'company:id,name',
            'client:id,name',
            'items.batch:id,name',
            'items.stocking:id,quantity,average_weight',
        ])
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId'])
            )
            ->when(
                ! empty($filters['clientId']),
                static fn ($q) => $q->where('client_id', $filters['clientId']),
            )
            ->when(
                ! empty($filters['batchId']),
                static fn ($q) => $q->where('batch_id', $filters['batchId']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    SaleStatus::from((string) $filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['dateFrom']),
                static fn ($q) => $q->whereDate('sale_date', '>=', $filters['dateFrom']),
            )
            ->when(
                ! empty($filters['dateTo']),
                static fn ($q) => $q->whereDate('sale_date', '<=', $filters['dateTo']),
            )
            ->latest('sale_date')
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    /**
     * Total kg já vendido de um stocking, agregado via sale_items.
     * Exclui vendas canceladas e soft-deleted.
     */
    public function soldWeightByStocking(string $stockingId, ?string $excludeSaleId = null): float
    {
        return (float) SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.stocking_id', $stockingId)
            ->whereNotIn('sales.status', [SaleStatus::CANCELLED->value])
            ->whereNull('sales.deleted_at')
            ->when(
                $excludeSaleId !== null,
                static fn ($q) => $q->where('sale_items.sale_id', '!=', $excludeSaleId),
            )
            ->sum('sale_items.total_weight');
    }

    public function findOrFailLocked(string $id): Sale
    {
        return Sale::with([
            'company:id,name',
            'client:id,name',
            'stocking',
            'items.batch:id,name',
            'items.stocking:id,quantity,average_weight',
        ])->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    /**
     * @return Collection<int, Sale>
     */
    public function findByOrderId(string $orderId): Collection
    {
        return Sale::with([
            'company:id,name',
            'client:id,name',
            'stocking',
            'items.batch:id,name',
            'items.stocking:id,quantity,average_weight',
        ])->where('sales_order_id', $orderId)->get();
    }
}
