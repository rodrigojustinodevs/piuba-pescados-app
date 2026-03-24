<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;

final readonly class ShowFinancialTransactionUseCase
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): FinancialTransaction
    {
        return $this->repository->findOrFail($id);
    }
}
