<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final readonly class CancelSaleAction
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private StockingRepositoryInterface $stockingRepository,
        private CancelSaleReceivablesAction $cancelReceivables,
        private RevertBiomassOutflowAction $revertBiomassOutflow,
        private ReopenStockingAndBatchAction $reopenStockingAndBatch,
    ) {
    }

    /**
     * Desfaz os efeitos colaterais de uma venda e a marca como cancelada.
     *
     * Deve ser chamada dentro de uma DB::transaction existente
     * — não abre transação própria para permitir execução em lote
     * (ex: cancelamento de todas as vendas de um pedido).
     *
     * Passo 1 — Cancela recebíveis (bloqueia se pago → SaleFinanciallyLockedException)
     * Passo 2 — Estorna biomassa (contra-lançamento IN + soft-delete no OUT)
     * Passo 3 — Reabre stocking/batch se era despesca total
     * Passo 4 — Marca a venda como CANCELLED
     *
     * @throws \App\Domain\Exceptions\SaleFinanciallyLockedException
     */
    public function execute(Sale $sale): void
    {
        // Passo 1: trava financeira — se pago, para aqui antes de qualquer outra escrita
        $this->cancelReceivables->execute((string) $sale->id);

        // Passo 2: estorno de biomassa
        $this->revertBiomassOutflow->execute($sale);

        // Passo 3: reabre ciclo de vida apenas se era despesca total e stocking ainda fechado
        if ($sale->stocking_id !== null && (bool) $sale->is_total_harvest) {
            $stocking = $this->stockingRepository->findOrFailLocked((string) $sale->stocking_id);

            if ($stocking->isClosed()) {
                $this->reopenStockingAndBatch->execute($stocking, (string) $sale->batch_id);
            }
        }

        // Passo 4: cancela a venda
        $this->saleRepository->update(
            (string) $sale->id,
            [
                'status' => SaleStatus::CANCELLED->value,
            ]
        );
    }
}
