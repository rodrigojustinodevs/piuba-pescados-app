<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Exceptions\SaleFinanciallyLockedException;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class CancelSaleReceivablesAction
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    /**
     * Cancela todas as transações financeiras vinculadas à venda.
     *
     * Trava financeira: se qualquer transação estiver paga (paid),
     * a venda não pode ser deletada — lança SaleFinanciallyLockedException.
     *
     * Transações overdue ou pending são canceladas normalmente.
     * Transações já canceladas são ignoradas (idempotência).
     *
     * @throws SaleFinanciallyLockedException
     */
    public function execute(string $saleId): void
    {
        /** @var Collection<int, FinancialTransaction> $transactions */
        $transactions = $this->transactionRepository->findLockedBySaleId($saleId);

        if ($transactions->isEmpty()) {
            return;
        }

        foreach ($transactions as $tx) {
            // Paga = dinheiro já entrou no caixa → não pode desfazer
            if ($tx->status === FinancialTransactionStatus::PAID) {
                throw new SaleFinanciallyLockedException();
            }
        }

        foreach ($transactions as $tx) {
            if ($tx->status === FinancialTransactionStatus::CANCELLED) {
                continue; // Já cancelada — nada a fazer
            }

            $this->transactionRepository->update((string) $tx->id, [
                'status' => FinancialTransactionStatus::CANCELLED->value,
            ]);
        }
    }
}
