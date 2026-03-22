<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\PurchaseDTO;
use App\Application\DTOs\PurchaseItemDTO;
use App\Domain\Models\Purchase;
use DateTimeInterface;

final class PurchaseMapper
{
    public function __construct(
        private readonly PurchaseItemMapper $itemMapper,
    ) {}

    /**
     * Converte um Eloquent Model para DTO de resposta (saída da API).
     * Não é responsável por construir DTOs de entrada — isso é feito via PurchaseDTO::fromArray().
     */
    public function toResponseDTO(Purchase $purchase): PurchaseDTO
    {
        $items = $purchase->items
            ->map(static fn ($item): PurchaseItemDTO => new PurchaseItemDTO(
                supplyId:   (string) $item->supply_id,
                quantity:   (float)  $item->quantity,
                unit:       (string) $item->unit,
                unitPrice:  (float)  $item->unit_price,
                id:         (string) $item->id,
                totalPrice: (float)  $item->total_price,
            ))
            ->all();

        return new PurchaseDTO(
            companyId:     (string) $purchase->company_id,
            supplierId:    (string) $purchase->supplier_id,
            purchaseDate:  $this->formatDate($purchase->purchase_date, 'Y-m-d'),
            status:        \App\Domain\Enums\PurchaseStatus::from($purchase->status),
            items:         $this->itemMapper->toDTOCollection($purchase->items),
            invoiceNumber: $purchase->invoice_number,
            receivedAt:    $this->formatDate($purchase->received_at),
        );
    }

    private function formatDate(
        null|string|DateTimeInterface $date,
        string $format = 'Y-m-d H:i:s',
    ): ?string {
        if ($date instanceof DateTimeInterface) {
            return $date->format($format);
        }

        return $date !== null && $date !== '' ? (string) $date : null;
    }
}