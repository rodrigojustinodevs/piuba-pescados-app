<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FinancialTransactionDTO;
use App\Application\UseCases\FinancialTransaction\CreateFinancialTransactionUseCase;
use App\Application\UseCases\FinancialTransaction\DeleteFinancialTransactionUseCase;
use App\Application\UseCases\FinancialTransaction\ListFinancialTransactionsUseCase;
use App\Application\UseCases\FinancialTransaction\ShowFinancialTransactionUseCase;
use App\Application\UseCases\FinancialTransaction\UpdateFinancialTransactionUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinancialTransactionService
{
    public function __construct(
        protected CreateFinancialTransactionUseCase $createFinancialTransactionUseCase,
        protected ListFinancialTransactionsUseCase  $listFinancialTransactionsUseCase,
        protected ShowFinancialTransactionUseCase   $showFinancialTransactionUseCase,
        protected UpdateFinancialTransactionUseCase $updateFinancialTransactionUseCase,
        protected DeleteFinancialTransactionUseCase $deleteFinancialTransactionUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FinancialTransactionDTO
    {
        return $this->createFinancialTransactionUseCase->execute($data);
    }

    public function showAllFinancialTransactions(): AnonymousResourceCollection
    {
        return $this->listFinancialTransactionsUseCase->execute();
    }

    public function showFinancialTransaction(string $id): ?FinancialTransactionDTO
    {
        return $this->showFinancialTransactionUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFinancialTransaction(string $id, array $data): FinancialTransactionDTO
    {
        return $this->updateFinancialTransactionUseCase->execute($id, $data);
    }

    public function deleteFinancialTransaction(string $id): bool
    {
        return $this->deleteFinancialTransactionUseCase->execute($id);
    }
}
