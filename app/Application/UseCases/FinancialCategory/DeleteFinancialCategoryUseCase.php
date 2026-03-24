<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Exceptions\FinancialCategoryHasTransactionsException;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteFinancialCategoryUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    /**
     * Attempts a hard delete.
     *
     * Throws FinancialCategoryHasTransactionsException when the category still
     * has linked financial transactions — the caller should handle this by
     * offering to deactivate the category instead.
     */
    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        if ($this->repository->hasTransactions($id)) {
            throw new FinancialCategoryHasTransactionsException($id);
        }

        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}
