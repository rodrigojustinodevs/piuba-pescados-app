<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SalesOrderDTO;
use App\Application\DTOs\SalesOrderItemDTO;
use App\Application\DTOs\SalesOrderUpdateDTO;
use App\Application\DTOs\SalesQuotationDTO;
use App\Domain\Enums\SalesOrderStatus;
use App\Domain\Enums\SalesOrderType;
use App\Domain\Models\SalesOrder;
use App\Domain\Models\SalesOrderItem;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SalesOrderRepositoryInterface;
use Illuminate\Support\Str;

final class SalesOrderRepository implements SalesOrderRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'items.stocking:id,quantity,average_weight,estimated_biomass,accumulated_fixed_cost',
        'client:id,name',
        'company:id,name',
    ];

    public function findOrFail(string $id): SalesOrder
    {
        return SalesOrder::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function findForCompanyOrFail(string $id, string $companyId): SalesOrder
    {
        return SalesOrder::query()
            ->with(self::DEFAULT_RELATIONS)
            ->whereKey($id)
            ->where('company_id', $companyId)
            ->firstOrFail();
    }

    /**
     * Persiste o pedido e seus itens sem abrir transação.
     * A transação é controlada pelo UseCase que chamou este método.
     *
     * total_amount já vem calculado pelo DTO — o repositório não recalcula.
     */
    public function createWithItems(SalesOrderDTO $dto): SalesOrder
    {
        /** @var SalesOrder $order */
        $order = SalesOrder::create($dto->toPersistence());

        $items = array_map(
            static fn (SalesOrderItemDTO $item): array => array_merge(
                ['id' => (string) Str::uuid()],
                $item->toPersistence((string) $order->id),
            ),
            $dto->items,
        );

        SalesOrderItem::insert($items);

        return $order->load(self::DEFAULT_RELATIONS);
    }

    public function createQuotationWithItems(SalesQuotationDTO $dto): SalesOrder
    {
        /** @var SalesOrder $order */
        $order = SalesOrder::create(array_merge($dto->toPersistence(), [
            'type'   => SalesOrderType::QUOTATION->value,
            'status' => SalesOrderStatus::DRAFT->value,
        ]));

        $items = array_map(
            static fn (SalesOrderItemDTO $item): array => array_merge(
                ['id' => (string) Str::uuid()],
                $item->toPersistence((string) $order->id),
            ),
            $dto->items,
        );

        SalesOrderItem::insert($items);

        return $order->load(self::DEFAULT_RELATIONS);
    }

    public function syncItems(SalesOrder $order, array $itemDTOs): void
    {
        $existing    = $order->items->keyBy('id');
        $incomingIds = collect($itemDTOs)
            ->map(static fn (SalesOrderItemDTO $dto): ?string => $dto->id)
            ->filter()
            ->values();

        $existing
            ->reject(static fn ($item): bool => $incomingIds->contains($item->id))
            ->each(static fn ($item): bool => $item->delete());

        foreach ($itemDTOs as $dto) {
            if ($dto->id !== null && $existing->has($dto->id)) {
                $existing->get($dto->id)->update($dto->toPersistence((string) $order->id));
            }
        }
    }

    public function replaceItems(SalesOrder $order, array $itemDTOs): void
    {
        $order->items()->delete();

        if ($itemDTOs === []) {
            return;
        }

        $rows = array_map(
            static fn (SalesOrderItemDTO $item): array => array_merge(
                ['id' => (string) Str::uuid()],
                $item->toPersistence((string) $order->id),
            ),
            $itemDTOs,
        );

        SalesOrderItem::insert($rows);
    }

    public function updateQuotationWithItems(string $id, SalesOrderUpdateDTO $dto): SalesOrder
    {
        $order      = $this->findOrFail($id);
        $attributes = $dto->toScalarAttributes();

        if ($attributes !== []) {
            $order->update($attributes);
        }

        // Substitui os itens apenas quando o payload incluiu 'items'
        if ($dto->items !== null) {
            // Delete dos itens atuais (soft-delete via Model ou hard-delete se sem deleted_at)
            $order->items()->delete();

            // Bulk insert dos novos itens
            $rows = array_map(
                static fn (SalesOrderItemDTO $item): array => array_merge(
                    ['id' => (string) Str::uuid()],
                    $item->toPersistence((string) $order->id),
                ),
                $dto->items,
            );

            SalesOrderItem::insert($rows);
        }

        return $order->refresh()->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): SalesOrder
    {
        $order = $this->findOrFail($id);

        if ($attributes !== []) {
            $order->update($attributes);
        }

        return $order->refresh()->load(self::DEFAULT_RELATIONS);
    }

    public function paginate(array $filters): PaginationInterface
    {
        $paginator = SalesOrder::with(['client:id,name'])
            ->where('company_id', $filters['companyId'])
            ->when(
                ! empty($filters['clientId']),
                static fn ($q) => $q->where('client_id', $filters['clientId']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    SalesOrderStatus::from((string) $filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where(
                    'type',
                    SalesOrderType::from((string) $filters['type'])->value,
                ),
            )
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}
