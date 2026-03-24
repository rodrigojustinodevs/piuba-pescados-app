<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteFinancialTransactionUseCase
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}
