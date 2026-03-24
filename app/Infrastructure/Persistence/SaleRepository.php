<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SaleInputDTO;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;

final class SaleRepository implements SaleRepositoryInterface
{
    public function create(SaleInputDTO $dto): Sale
    {
        /** @var Sale $sale */
        $sale = Sale::create([
            'company_id'            => $dto->companyId,
            'client_id'             => $dto->clientId,
            'batch_id'              => $dto->batchId,
            'stocking_id'           => $dto->stockingId,
            'financial_category_id' => $dto->financialCategoryId,
            'total_weight'          => $dto->totalWeight,
            'price_per_kg'          => $dto->pricePerKg,
            'total_revenue'         => $dto->totalRevenue(),
            'sale_date'             => $dto->saleDate,
            'status'                => $dto->status->value,
            'notes'                 => $dto->notes,
            'is_total_harvest'      => $dto->isHarvestTotal,
        ]);

        return $sale->load(['company:id,name', 'client:id,name', 'batch:id,name', 'stocking']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sale
    {
        $sale = $this->findOrFail($id);

        $sale->update($attributes);

        return $sale->refresh()->load(['company:id,name', 'client:id,name', 'batch:id,name', 'stocking']);
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
            'batch:id,name',
            'stocking',
        ])->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     client_id?: string|null,
     *     batch_id?: string|null,
     *     status?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Sale::with([
            'company:id,name',
            'client:id,name',
            'batch:id,name',
        ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['client_id']),
                static fn ($q) => $q->where('client_id', $filters['client_id']),
            )
            ->when(
                ! empty($filters['batch_id']),
                static fn ($q) => $q->where('batch_id', $filters['batch_id']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    SaleStatus::from((string) $filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('sale_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('sale_date', '<=', $filters['date_to']),
            )
            ->latest('sale_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function soldWeightByStocking(string $stockingId, ?string $excludeSaleId = null): float
    {
        return (float) Sale::where('stocking_id', $stockingId)
            ->whereNotIn('status', [SaleStatus::CANCELLED->value])
            ->when(
                $excludeSaleId !== null,
                static fn ($q) => $q->where('id', '!=', $excludeSaleId),
            )
            ->sum('total_weight');
    }
}
