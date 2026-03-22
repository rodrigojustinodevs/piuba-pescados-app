<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\StockDTO;
use App\Domain\Models\Stock;
use DateTimeInterface;

final class StockMapper
{
    public function toDTO(Stock $stock): StockDTO
    {
        return new StockDTO(
            id:                 (string) $stock->id,
            companyId:          (string) $stock->company_id,
            supplyId:           (string) $stock->supply_id,
            currentQuantity:    (float)  $stock->current_quantity,
            unit:               (string) $stock->unit,
            unitPrice:          (float)  $stock->unit_price,
            minimumStock:       (float)  $stock->minimum_stock,
            withdrawalQuantity: (float)  $stock->withdrawal_quantity,
            supplierId:         $stock->supplier_id ? (string) $stock->supplier_id : null,
            createdAt:          $this->formatDate($stock->created_at),
            updatedAt:          $this->formatDate($stock->updated_at),
        );
    }

    private function formatDate(null | string | DateTimeInterface $date): ?string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        return $date !== null && $date !== '' ? $date : null;
    }
}
