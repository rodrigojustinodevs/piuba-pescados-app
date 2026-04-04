<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

/**
 * DTO específico para o fluxo de despesca/venda com validação biológica,
 * baixa de estoque e gestão do ciclo de vida do lote.
 */
final class HarvestSaleDTO
{
    public function __construct(
        public readonly string    $companyId,
        public readonly string    $clientId,
        public readonly string    $batchId,
        public readonly float     $totalWeight,
        public readonly float     $pricePerKg,
        public readonly string    $saleDate,
        public readonly bool      $isHarvestTotal,
        public readonly ?string   $stockingId = null,
        public readonly ?string   $financialCategoryId = null,
        public readonly SaleStatus $status = SaleStatus::PENDING,
        public readonly ?string   $notes = null,
        public readonly float     $tolerancePercent = 5.0,
        public readonly bool      $needsInvoice = false,
        public readonly bool      $requiresInvoice = false,
    ) {
    }

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /**
     * Limite superior de biomassa incluindo a margem de tolerância.
     * Ex: biomassa = 1000 kg, tolerance = 5% → limite = 1050 kg.
     */
    public function biomassLimitWithTolerance(float $availableBiomass): float
    {
        return $availableBiomass * (1 + $this->tolerancePercent / 100);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:            (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            batchId:             (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            totalWeight:         (float)  ($data['total_weight'] ?? $data['totalWeight'] ?? 0),
            pricePerKg:          (float)  ($data['price_per_kg'] ?? $data['pricePerKg'] ?? 0),
            saleDate:            (string) ($data['sale_date'] ?? $data['saleDate'] ?? ''),
            isHarvestTotal:      (bool)   ($data['is_total_harvest'] ?? $data['isHarvestTotal'] ?? false),
            stockingId:          isset($data['stocking_id']) ? (string) $data['stocking_id']
                               : (isset($data['stockingId']) ? (string) $data['stockingId'] : null),
            financialCategoryId: isset($data['financial_category_id']) ? (string) $data['financial_category_id']
                               : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            status:              isset($data['status'])
                               ? SaleStatus::from((string) $data['status'])
                               : SaleStatus::PENDING,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            tolerancePercent:    (float)  ($data['tolerance_percent'] ?? $data['tolerancePercent'] ?? 5.0),
            needsInvoice:        (bool)   ($data['needs_invoice'] ?? $data['needsInvoice'] ?? false),
            requiresInvoice:     (bool)   ($data['requires_invoice'] ?? $data['requiresInvoice'] ?? false),
        );
    }

    public function toSaleInputDTO(): SaleInputDTO
    {
        return new SaleInputDTO(
            companyId:           $this->companyId,
            clientId:            $this->clientId,
            batchId:             $this->batchId,
            totalWeight:         $this->totalWeight,
            pricePerKg:          $this->pricePerKg,
            saleDate:            $this->saleDate,
            stockingId:          $this->stockingId,
            financialCategoryId: $this->financialCategoryId,
            status:              $this->status,
            notes:               $this->notes,
            isHarvestTotal:      $this->isHarvestTotal,
            requiresInvoice:     $this->requiresInvoice,
        );
    }
}
