<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Sale\CancelSaleReceivablesAction;
use App\Application\Actions\Sale\ReopenStockingAndBatchAction;
use App\Application\Actions\Sale\RevertBiomassOutflowAction;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class CancelSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface       $saleRepository,
        private readonly StockingRepositoryInterface   $stockingRepository,
        private readonly CancelSaleReceivablesAction   $cancelReceivables,
        private readonly RevertBiomassOutflowAction    $revertBiomassOutflow,
        private readonly ReopenStockingAndBatchAction  $reopenStockingAndBatch,
    ) {}

    /**
     * Desfaz uma venda de forma atômica revertendo todos os efeitos colaterais:
     *
     *   Passo 1 — Trava financeira
     *             Cancela os Contas a Receber (pending/overdue → cancelled).
     *             Se algum estiver pago → SaleFinanciallyLockedException.
     *
     *   Passo 2 — Estorno de biomassa
     *             Cria contra-lançamento IN no livro-razão e soft-delete
     *             na transação OUT original, restaurando o saldo do stocking.
     *
     *   Passo 3 — Reabertura de ciclo de vida
     *             Se a venda era is_total_harvest=true e fechou o stocking/batch,
     *             reabre ambos (e o tank associado, se necessário).
     *
     *   Passo 4 — Soft delete da venda
     */
    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {

            // Lock pessimista — evita deleção concorrente ou edição simultânea
            /** @var Sale $sale */
            $sale = $this->saleRepository->findOrFailLocked($id);

            // ── Passo 1: Cancela recebíveis ───────────────────────────────────
            // Verificação ANTES de qualquer outra ação — se estiver pago, para aqui
            $this->cancelReceivables->execute((string) $sale->id);

            // ── Passo 2: Estorno de biomassa ──────────────────────────────────
            // Cria contra-lançamento IN e soft-delete no OUT original
            $this->revertBiomassOutflow->execute($sale);

            // ── Passo 3: Reabertura do ciclo de vida ──────────────────────────
            if ($sale->stocking_id !== null && (bool) $sale->is_total_harvest) {
                $stocking = $this->resolveLockedStocking((string) $sale->stocking_id);

                // Só reabre se ainda estiver fechado
                // (outra venda posterior pode já ter reaberto)
                if ($stocking !== null && $stocking->isClosed()) {
                    $this->reopenStockingAndBatch->execute(
                        $stocking,
                        (string) $sale->batch_id,
                    );
                }
            }

            // ── Passo 4: Cancela a venda ──────────────────────────────────────
            $this->saleRepository->update((string) $sale->id, [
                'status' => SaleStatus::CANCELLED,
            ]);
        });
    }

    /**
     * Busca o stocking com lock pessimista via repositório.
     * Retorna null se o stocking_id for inválido (defensivo — o request já validou).
     */
    private function resolveLockedStocking(string $stockingId): ?Stocking
    {
        return $this->stockingRepository->findOrFailLocked($stockingId);
    }
}