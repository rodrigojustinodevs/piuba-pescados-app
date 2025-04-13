<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Application\DTOs\FinancialTransactionDTO;
use App\Domain\Enums\FinancialType;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class ShowFinancialTransactionUseCase
{
    public function __construct(
        protected FinancialTransactionRepositoryInterface $financialTransactionRepository
    ) {
    }

    public function execute(string $id): ?FinancialTransactionDTO
    {
        $transaction = $this->financialTransactionRepository->showFinancialTransaction('id', $id);

        if (! $transaction instanceof FinancialTransaction) {
            throw new RuntimeException('Financial transaction not found');
        }

        $transactionDate = $transaction->transaction_date instanceof Carbon
            ? $transaction->transaction_date
            : Carbon::parse($transaction->transaction_date);

        return new FinancialTransactionDTO(
            id: $transaction->id,
            type: FinancialType::from($transaction->type),
            description: $transaction->description,
            amount: (float) $transaction->amount,
            transactionDate: $transactionDate->toDateString(),
            company: [
                'name' => $transaction->company->name ?? '',
            ],
            category: [
                'id'   => $transaction->category->id ?? '',
                'name' => $transaction->category->name ?? '',
            ],
            createdAt: $transaction->created_at?->toDateTimeString(),
            updatedAt: $transaction->updated_at?->toDateTimeString()
        );
    }
}
