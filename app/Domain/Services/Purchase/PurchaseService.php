<?php

declare(strict_types=1);

namespace App\Domain\Services\Purchase;

use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Domain\Services\Stock\StockService;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $purchaseRepository,
        private readonly StockService $stockService
    ) {}

    /**
     * Registra a compra e atualiza o estoque automaticamente.
     */
    public function createPurchase(array $data): Purchase
    {
        // 1. Criamos o registro da compra para histórico financeiro
        $purchase = $this->purchaseRepository->create([
            'company_id'   => $data['company_id'],
            'supplier_id'  => $data['supplier_id'],
            'stocking_id'  => $data['stocking_id'] ?? null,
            'supply_name'  => $data['supply_name'],
            'quantity'     => $data['quantity'],
            'unit'         => $data['unit'],
            'total_price'  => $data['total_price'],
            'purchase_date'=> $data['purchase_date'],
        ]);

        // 2. Acionamos o StockService para atualizar o saldo e o preço médio
        // Passamos o total_price para que o StockService calcule o PMP real (com fretes/taxas)
        $this->stockService->addEntry(
            companyId: $purchase->company_id,
            quantity: (float) $purchase->quantity,
            totalCost: (float) $purchase->total_price,
            unitPrice: 0.0, // O addEntry prioriza o totalCost se for > 0
            unit: $purchase->unit,
            supplierId: $purchase->supplier_id
        );

        return $purchase;
    }
}