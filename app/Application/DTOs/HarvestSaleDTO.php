<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\PaymentMethod;
use App\Domain\Enums\SaleStatus;

/**
 * DTO do fluxo de despesca/venda.
 *
 * Suporta múltiplos itens via items[]. Cada item representa um produto
 * (espécie/lote) com seu próprio stocking_id e peso vendido.
 *
 * Para compatibilidade com payloads legados, fromValidated() normaliza
 * automaticamente campos diretos (total_weight, stocking_id, etc.) para items[0].
 */
final readonly class HarvestSaleDTO
{
    /**
     * @param array<int, SaleItemDTO> $items
     */
    public function __construct(
        public string $companyId,
        public string $clientId,
        public array $items,
        public string $saleDate,
        public SaleStatus $status,
        public bool $isHarvestTotal = false,
        public ?string $dueDate = null,
        public ?string $paidAt = null,
        public ?string $financialCategoryId = null,
        public ?string $notes = null,
        public bool $needsInvoice = false,
        public ?string $paymentMethod = null,
        public ?string $invoiceNumber = null,
        public float $discount = 0.0,
        public float $freight = 0.0,
        public float $taxes = 0.0,
    ) {
    }

    public function totalRevenue(): float
    {
        $subtotals = array_map(static fn (SaleItemDTO $item): float => $item->subtotal(), $this->items);

        return round((float) array_sum($subtotals), 2);
    }

    public function firstItem(): SaleItemDTO
    {
        return $this->items[0];
    }

    /**
     * Constrói o DTO a partir do array validado e normalizado pela SaleStoreRequest.
     * Aceita tanto o formato novo (items[]) quanto o legado (campos diretos).
     *
     * @param array<string, mixed> $data
     */
    public static function fromValidated(array $data): self
    {
        if (isset($data['items']) && is_array($data['items'])) {
            $items = array_map(
                static fn (array $item): SaleItemDTO => SaleItemDTO::fromArray($item),
                $data['items'],
            );
        } else {
            // Formato legado: campos diretos → item único
            $items = [SaleItemDTO::fromArray([
                'batch_id'         => $data['batch_id'] ?? '',
                'stocking_id'      => $data['stocking_id'] ?? '',
                'total_weight'     => $data['total_weight'] ?? 0,
                'price_per_kg'     => $data['price_per_kg'] ?? 0,
                'is_total_harvest' => $data['is_total_harvest'] ?? false,
            ])];
        }

        // is_total_harvest do header é o OR de todos os itens (para compatibilidade)
        $isHarvestTotal = (bool) ($data['is_total_harvest'] ?? false)
            || array_reduce($items, static fn (bool $carry, SaleItemDTO $i): bool => $carry || $i->isHarvestTotal, false);

        return new self(
            companyId:           (string) ($data['company_id'] ?? ''),
            clientId:            (string)  $data['client_id'],
            items:               $items,
            saleDate:            (string)  $data['sale_date'],
            status:              isset($data['status'])
                                     ? SaleStatus::from((string) $data['status'])
                                     : SaleStatus::PENDING,
            isHarvestTotal:      $isHarvestTotal,
            dueDate:             isset($data['due_date']) ? (string) $data['due_date'] : null,
            paidAt:              isset($data['paid_at']) ? (string) $data['paid_at'] : null,
            financialCategoryId: isset($data['financial_category_id'])
                                     ? (string) $data['financial_category_id']
                                     : null,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            needsInvoice:        (bool) ($data['needs_invoice'] ?? false),
            paymentMethod:       isset($data['payment_method']) ? (string) $data['payment_method'] : null,
            invoiceNumber:       isset($data['invoice_number']) ? (string) $data['invoice_number']
                                     : (isset($data['numberNf']) ? (string) $data['numberNf'] : null),
            discount:            (float) ($data['discount'] ?? 0),
            freight:             (float) ($data['freight'] ?? $data['shipping'] ?? 0),
            taxes:               (float) ($data['taxes'] ?? 0),
        );
    }

    /**
     * Converte para SaleInputDTO para persistência via SaleRepository.
     */
    public function toSaleInputDTO(): SaleInputDTO
    {
        return new SaleInputDTO(
            companyId:           $this->companyId,
            clientId:            $this->clientId,
            saleDate:            $this->saleDate,
            items:               $this->items,
            financialCategoryId: $this->financialCategoryId,
            status:              $this->status,
            notes:               $this->notes,
            needsInvoice:        $this->needsInvoice,
            paymentMethod:       $this->paymentMethod !== null
                                     ? PaymentMethod::from($this->paymentMethod)
                                     : null,
            invoiceNumber:       $this->invoiceNumber,
            discount:            $this->discount,
            shipping:            $this->freight,
            taxes:               $this->taxes,
            dueDate:             $this->dueDate,
            paidAt:              $this->paidAt,
        );
    }
}
