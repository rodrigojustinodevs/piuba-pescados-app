<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteFinancialTransactionUseCase
{
    public function __construct(
        protected FinancialTransactionRepositoryInterface $financialTransactionRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->financialTransactionRepository->delete($id));
    }
}
