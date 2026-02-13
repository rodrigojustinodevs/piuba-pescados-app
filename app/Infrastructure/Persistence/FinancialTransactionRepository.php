<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FinancialTransactionRepository implements FinancialTransactionRepositoryInterface
{
    /**
     * Create a new financial category.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FinancialTransaction
    {
        return FinancialTransaction::create($data);
    }

    /**
     * Update an existing financial category.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FinancialTransaction
    {
        $financialTransaction = FinancialTransaction::find($id);

        if ($financialTransaction) {
            $financialTransaction->update($data);

            return $financialTransaction;
        }

        return null;
    }

    /**
     * Get paginated financial categories.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, FinancialTransaction> $paginator */
        $paginator = FinancialTransaction::with([
            'company:id,name',
            'category:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show financial category by field and value.
     */
    public function showFinancialTransaction(string $field, string | int $value): ?FinancialTransaction
    {
        return FinancialTransaction::where($field, $value)->first();
    }

    /**
     * Delete a financial category.
     */
    public function delete(string $id): bool
    {
        $financialTransaction = FinancialTransaction::find($id);

        if (! $financialTransaction) {
            return false;
        }

        return (bool) $financialTransaction->delete();
    }
}
