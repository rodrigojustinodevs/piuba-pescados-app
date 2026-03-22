<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Domain\Models\StockTransaction;
use DateTimeInterface;

final class StockTransactionMapper
{
    /** @return array<string, mixed> */
    public function toArray(StockTransaction $transaction): array
    {
        return [
            'id'            => $transaction->id,
            'stockId'       => $transaction->stock_id,
            'companyId'     => $transaction->company_id,
            'supplierId'    => $transaction->supplier_id,
            'referenceId'   => $transaction->reference_id,
            'referenceType' => $transaction->reference_type,
            'quantity'      => (float) $transaction->quantity,
            'unitPrice'     => (float) $transaction->unit_price,
            'totalCost'     => (float) $transaction->total_cost,
            'unit'          => $transaction->unit,
            'direction'     => $transaction->direction,
            'createdAt'     => $this->formatDate($transaction->created_at),
        ];
    }

    private function formatDate(null | string | DateTimeInterface $date): ?string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        return $date !== null && $date !== '' ? $date : null;
    }
}
