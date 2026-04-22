<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesOrder;

use App\Application\Actions\Sale\CancelSaleAction;
use App\Domain\Enums\SalesOrderStatus;
use App\Domain\Exceptions\SalesOrderNotCancellableException;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\SalesOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CancelSalesOrderUseCase
{
    public function __construct(
        private SalesOrderRepositoryInterface $salesOrderRepository,
        private SaleRepositoryInterface $saleRepository,
        private CancelSaleAction $cancelSale,
    ) {
    }

    /**
     * Cancela o pedido e todas as vendas vinculadas em uma única transação.
     *
     * Ordem de operações:
     *  1. Verifica se o pedido pode ser cancelado (fail-fast fora da transação)
     *  2. Abre transação única — evita transações aninhadas por venda
     *  3. Para cada venda: delega para CancelSaleAction (mesma lógica do CancelSaleUseCase)
     *  4. Atualiza status do pedido
     *
     * @throws SalesOrderNotCancellableException
     * @throws \App\Domain\Exceptions\SaleFinanciallyLockedException Se qualquer venda estiver paga
     */
    public function execute(string $orderId): void
    {
        $order = $this->salesOrderRepository->findOrFail($orderId);

        // Fail-fast fora da transação — sem custo de lock se o pedido não pode ser cancelado
        if (! $order->canBeCancelled()) {
            throw new SalesOrderNotCancellableException($orderId);
        }

        DB::transaction(
            function () use ($order, $orderId): void {
                // Todas as vendas do pedido em uma única query
                $sales = $this->saleRepository->findByOrderId((string) $order->id);

                foreach ($sales as $sale) {
                    // Delega os 4 passos para CancelSaleAction
                    // Não abre sub-transação — já estamos dentro da transação do pedido
                    $this->cancelSale->execute($sale);
                }

                // Atualiza o pedido após todas as vendas canceladas com sucesso
                $this->salesOrderRepository->update(
                    $orderId,
                    [
                        'status' => SalesOrderStatus::CANCELLED->value,
                    ]
                );
            }
        );
    }
}
