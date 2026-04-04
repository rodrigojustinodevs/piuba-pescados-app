<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Support\Collection;

final class FinancialTransactionRepository implements FinancialTransactionRepositoryInterface
{
    public function create(FinancialTransactionInputDTO $dto): FinancialTransaction
    {
        /** @var FinancialTransaction $transaction */
        $transaction = FinancialTransaction::create([
            'company_id'            => $dto->companyId,
            'financial_category_id' => $dto->financialCategoryId,
            'type'                  => $dto->type->value,
            'status'                => $dto->status->value,
            'amount'                => $dto->amount,
            'due_date'              => $dto->dueDate,
            'payment_date'          => $dto->paymentDate,
            'description'           => $dto->description,
            'notes'                 => $dto->notes,
            'reference_type'        => $dto->referenceType?->value,
            'reference_id'          => $dto->referenceId,
        ]);

        return $transaction->load(['company:id,name', 'category:id,name,type']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialTransaction
    {
        $transaction = $this->findOrFail($id);

        $transaction->update($attributes);

        return $transaction->refresh()->load(['company:id,name', 'category:id,name,type']);
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findOrFail(string $id): FinancialTransaction
    {
        return FinancialTransaction::with([
            'company:id,name',
            'category:id,name,type',
        ])->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     status?: string|null,
     *     type?: string|null,
     *     financial_category_id?: string|null,
     *     due_date_from?: string|null,
     *     due_date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = FinancialTransaction::with([
            'company:id,name',
            'category:id,name,type',
        ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    FinancialTransactionStatus::from((string) $filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where(
                    'type',
                    FinancialType::from((string) $filters['type'])->value,
                ),
            )
            ->when(
                ! empty($filters['financial_category_id']),
                static fn ($q) => $q->where('financial_category_id', $filters['financial_category_id']),
            )
            ->when(
                ! empty($filters['due_date_from']),
                static fn ($q) => $q->whereDate('due_date', '>=', $filters['due_date_from']),
            )
            ->when(
                ! empty($filters['due_date_to']),
                static fn ($q) => $q->whereDate('due_date', '<=', $filters['due_date_to']),
            )
            ->latest('due_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function showFinancialTransaction(string $field, string | int $value): ?FinancialTransaction
    {
        return FinancialTransaction::with([
            'company:id,name',
            'category:id,name,type',
        ])
            ->where($field, $value)
            ->first();
    }

    public function findLockedBySaleId(string $saleId): Collection
    {
        return FinancialTransaction::query()
            ->where('reference_type', FinancialTransactionReferenceType::SALE->value)
            ->where('reference_id', $saleId)
            ->lockForUpdate()
            ->get();
    }
}
