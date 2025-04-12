<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FinancialCategoryRepository implements FinancialCategoryRepositoryInterface
{
    /**
     * Create a new financial category.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FinancialCategory
    {
        return FinancialCategory::create($data);
    }

    /**
     * Update an existing financial category.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FinancialCategory
    {
        $financialCategory = FinancialCategory::find($id);

        if ($financialCategory) {
            $financialCategory->update($data);

            return $financialCategory;
        }

        return null;
    }

    /**
     * Get paginated financial categories.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<FinancialCategory> $paginator */
        $paginator = FinancialCategory::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show financial category by field and value.
     */
    public function showFinancialCategory(string $field, string | int $value): ?FinancialCategory
    {
        return FinancialCategory::where($field, $value)->first();
    }

    /**
     * Delete a financial category.
     */
    public function delete(string $id): bool
    {
        $financialCategory = FinancialCategory::find($id);

        if (! $financialCategory) {
            return false;
        }

        return (bool) $financialCategory->delete();
    }
}
