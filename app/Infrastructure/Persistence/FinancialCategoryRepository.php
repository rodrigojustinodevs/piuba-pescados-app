<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\FinancialCategoryInputDTO;
use App\Domain\Enums\FinancialCategoryStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\FinancialCategoryHasTransactionsException;
use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class FinancialCategoryRepository implements FinancialCategoryRepositoryInterface
{
    public function create(FinancialCategoryInputDTO $dto): FinancialCategory
    {
        /** @var FinancialCategory $category */
        $category = FinancialCategory::create([
            'company_id' => $dto->companyId,
            'name'       => $dto->name,
            'type'       => $dto->type->value,
            'status'     => $dto->status->value,
        ]);

        return $category->load('company:id,name');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialCategory
    {
        $category = $this->findOrFail($id);

        $category->update($attributes);

        return $category->refresh()->load('company:id,name');
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
        return FinancialCategory::with('company:id,name')->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     type?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = FinancialCategory::with('company:id,name')
            ->where('company_id', $filters['company_id'])
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
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function showFinancialCategory(string $field, string | int $value): ?FinancialCategory
    {
        return FinancialCategory::with('company:id,name')
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
