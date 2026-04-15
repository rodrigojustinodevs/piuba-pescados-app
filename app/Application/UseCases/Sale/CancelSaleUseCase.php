<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Sale\CancelSaleAction;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CancelSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private CancelSaleAction $cancelSale,
    ) {
    }

    /**
     * Cancela uma única venda de forma atômica.
     * Toda a lógica está em CancelSaleAction — reutilizada pelo CancelSalesOrderUseCase.
     *
     * @throws \App\Domain\Exceptions\SaleFinanciallyLockedException
     */
    public function execute(string $id): void
    {
        DB::transaction(
            function () use ($id): void {
                // Lock pessimista — evita cancelamento concorrente
                $sale = $this->saleRepository->findOrFailLocked($id);

                $this->cancelSale->execute($sale);
            }
        );
    }
}
