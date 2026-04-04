<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Exceptions\SaleFinanciallyLockedException;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Sincroniza o valor das transações financeiras vinculadas a uma venda
 * quando a receita é alterada no update.
 *
 * Fluxo:
 *  1. Busca transações com lockForUpdate via repositório.
 *  2. Lança SaleFinanciallyLockedException se alguma não for pending.
 *  3. Só escreve se o valor efetivamente mudou (evita UPDATE desnecessário).
 *
 * Chamada dentro de DB::transaction — atomicidade garantida pelo chamador.
 *
 * @throws SaleFinanciallyLockedException
 */
final class SyncReceivableAmountAction
{
    private const float EPSILON = 0.000_01;

    public function __construct(
        private readonly FinancialTransactionRepositoryInterface $transactionRepository,
    ) {}

    /**
     * @throws SaleFinanciallyLockedException
     */
    public function execute(string $saleId, float $newRevenue, float $oldRevenue): void
    {
        $transactions = $this->transactionRepository->findLockedBySaleId($saleId);

        if ($transactions->isEmpty()) {
            return;
        }

        $this->assertAllPending($transactions);

        if (! $this->hasChanged($newRevenue, $oldRevenue)) {
            return;
        }

        foreach ($transactions as $transaction) {
            $this->transactionRepository->update((string) $transaction->id, [
                'amount' => $newRevenue,
            ]);
        }
    }

    public function hasChanged(float $newRevenue, float $oldRevenue): bool
    {
        return abs($newRevenue - $oldRevenue) > self::EPSILON;
    }

    /**
     * @param Collection<int, FinancialTransaction> $transactions
     *
     * @throws SaleFinanciallyLockedException
     */
    private function assertAllPending(Collection $transactions): void
    {
        foreach ($transactions as $transaction) {
            if ($transaction->status !== FinancialTransactionStatus::PENDING) {
                throw new SaleFinanciallyLockedException();
            }
        }
    }
}