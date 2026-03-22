<?php

declare(strict_types=1);

namespace App\Application\Actions\Stock;

use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\StockTransactionRepositoryInterface;

final class RegisterStockTransactionAction
{
    public function __construct(
        private readonly StockTransactionRepositoryInterface $transactionRepository,
    ) {}

    public function execute(StockTransactionDTO $dto): StockTransaction
    {
        return $this->transactionRepository->create($dto);
    }
}