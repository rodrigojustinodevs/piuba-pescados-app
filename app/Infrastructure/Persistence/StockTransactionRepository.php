<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;
use Illuminate\Support\Str;

final class StockTransactionRepository implements StockTransactionRepositoryInterface
{
    public function create(StockTransactionDTO $dto): StockTransaction
    {
        return StockTransaction::create([
            'id'             => (string) Str::uuid(),
            'company_id'     => $dto->companyId,
            'supply_id'      => $dto->supplyId,
            'quantity'       => $dto->quantity,
            'unit_price'     => $dto->unitPrice,
            'total_cost'     => $dto->totalCost,
            'unit'           => $dto->unit->value,
            'direction'      => $dto->direction->value,
            'reference_id'   => $dto->referenceId,
            'reference_type' => $dto->referenceType?->value,
        ]);
    }

    public function findBy(string $field, string | int $value): ?StockTransaction
    {
        return StockTransaction::where($field, $value)->first();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = StockTransaction::query()
            ->with('supply')
            ->leftJoin('stocks', static function ($join): void {
                $join->on('stocks.supply_id', '=', 'stock_transactions.supply_id')
                    ->on('stocks.company_id', '=', 'stock_transactions.company_id')
                    ->whereNull('stocks.deleted_at');
            })
            ->select([
                'stock_transactions.*',
                'stocks.location',
                'stocks.responsible',
                'stocks.notes',
            ])
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('stock_transactions.company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['direction']),
                static fn ($q) => $q->where(
                    'direction',
                    StockTransactionDirection::from($filters['direction'])->value,
                ),
            )
            ->when(
                ! empty($filters['referenceType']),
                static fn ($q) => $q->where('reference_type', $filters['referenceType']),
            )
            ->when(
                ! empty($filters['referenceId']),
                static fn ($q) => $q->where('reference_id', $filters['referenceId']),
            )
            ->latest()
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): StockTransaction
    {
        $transaction = $this->findOrFail($id);
        $transaction->update($attributes);

        return $transaction->refresh();
    }

    public function delete(string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function findOrFail(string $id): StockTransaction
    {
        return StockTransaction::query()->findOrFail($id);
    }
}
