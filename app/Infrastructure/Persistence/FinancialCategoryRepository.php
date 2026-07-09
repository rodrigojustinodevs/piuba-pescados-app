<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\FinancialCategoryInputDTO;
use App\Domain\Enums\FinancialCategoryStatus;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\FinancialCategoryHasTransactionsException;
use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class FinancialCategoryRepository implements FinancialCategoryRepositoryInterface
{
    /** @return array<string, callable> */
    private function totalAmountSum(): array
    {
        return [
            'financialTransactions as total_amount' => static function ($q): void {
                $q->where('status', '!=', FinancialTransactionStatus::CANCELLED->value);
            },
        ];
    }

    /** @return array<string, callable> */
    private function withCompany(): array
    {
        return [
            'company' => static function ($q): void {
                $q->withTrashed()->select(['id', 'name']);
            },
        ];
    }

    public function create(FinancialCategoryInputDTO $dto): FinancialCategory
    {
        /** @var FinancialCategory $category */
        $category = FinancialCategory::create([
            'company_id' => $dto->companyId,
            'name'       => $dto->name,
            'type'       => $dto->type->value,
            'status'     => $dto->status->value,
            'notes'      => $dto->notes,
        ]);

        return $category->loadSum($this->totalAmountSum(), 'amount')->load($this->withCompany());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialCategory
    {
        $category = $this->findOrFail($id);

        $category->update($attributes);

        return $category->refresh()->loadSum($this->totalAmountSum(), 'amount')->load($this->withCompany());
    }

    /**
     * Throws FinancialCategoryHasTransactionsException when the category has
     * linked transactions instead of a hard delete.
     */
    public function delete(string $id): bool
    {
        $category = $this->findOrFail($id);

        if ($this->hasTransactions($id)) {
            throw new FinancialCategoryHasTransactionsException($id);
        }

        return (bool) $category->delete();
    }

    public function findOrFail(string $id): FinancialCategory
    {
        return FinancialCategory::with($this->withCompany())
            ->withSum($this->totalAmountSum(), 'amount')
            ->findOrFail($id);
    }

    /**
     * @param array{
     *     companyId: string,
     *     type?: string|null,
     *     status?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = FinancialCategory::with($this->withCompany())
            ->withSum($this->totalAmountSum(), 'amount')
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where(
                    'type',
                    FinancialType::from((string) $filters['type'])->value,
                ),
            )
            ->when(
                isset($filters['status']) && $filters['status'] !== '',
                static fn ($q) => $q->where(
                    'status',
                    FinancialCategoryStatus::from((string) $filters['status'])->value,
                ),
            )
            ->latest()
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function showFinancialCategory(string $field, string | int $value): ?FinancialCategory
    {
        return FinancialCategory::with($this->withCompany())
            ->withSum($this->totalAmountSum(), 'amount')
            ->where($field, $value)
            ->first();
    }

    public function hasTransactions(string $id): bool
    {
        return FinancialCategory::where('id', $id)
            ->whereHas('financialTransactions')
            ->exists();
    }
}
