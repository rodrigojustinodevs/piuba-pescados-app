<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Application\Actions\Stock\RegisterStockTransactionAction;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;
use App\Domain\Models\Sale;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\StockTransactionRepositoryInterface;

final readonly class RevertBiomassOutflowAction
{
    public function __construct(
        private StockTransactionRepositoryInterface $stockTransactionRepository,
        private RegisterStockTransactionAction $registerStockTransaction,
    ) {
    }

    /**
     * Estorna a baixa de biomassa gerada no momento da venda.
     *
     * Estratégia de estorno por contra-lançamento:
     *  - Busca a transação de saída (direction=out, reference_type=sale, reference_id=sale_id)
     *  - Cria uma nova transação de entrada (direction=in) com o mesmo quantity e unit_price
     *  - Soft-delete na transação original
     *
     * Contra-lançamento em vez de delete direto preserva o histórico financeiro
     * e mantém o CMV rastreável para auditorias.
     *
     * Se não houver transação de estoque vinculada (venda sem stocking_id),
     * retorna null silenciosamente.
     */
    public function execute(Sale $sale): ?StockTransaction
    {
        // Busca a transação de saída original desta venda
        $original = $this->stockTransactionRepository->findBy('reference_id', (string) $sale->id);

        if (! $original instanceof StockTransaction) {
            return null;
        }

        // Cria contra-lançamento (entrada) para neutralizar a saída
        $reversal = $this->registerStockTransaction->execute(new StockTransactionDTO(
            companyId:     (string) $sale->company_id,
            quantity:      (float)  $original->quantity,
            unitPrice:     (float)  $original->unit_price,
            totalCost:     (float)  $original->total_cost,
            unit:          Unit::from($original->unit),
            direction:     StockTransactionDirection::IN,
            supplyId:      null,
            referenceId:   (string) $sale->id,
            referenceType: StockTransactionReferenceType::SALE,
        ));

        // Soft-delete na transação original — mantida no histórico mas marcada como revertida
        $this->stockTransactionRepository->delete((string) $original->id);

        return $reversal;
    }
}
