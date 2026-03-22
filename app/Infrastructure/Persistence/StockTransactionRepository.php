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
            'unit'           => $dto->unit,
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
        $paginator = StockTransaction::where('stock_id', $filters['stock_id'])
            ->when(
                ! empty($filters['direction']),
                static fn ($q) => $q->where(
                    'direction',
                    StockTransactionDirection::from($filters['direction'])->value,
                ),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}
