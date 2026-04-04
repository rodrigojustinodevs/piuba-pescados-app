<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

final class SaleInputDTO
{
    public function __construct(
        public readonly string    $companyId,
        public readonly string    $clientId,
        public readonly string    $batchId,
        public readonly float     $totalWeight,
        public readonly float     $pricePerKg,
        public readonly string    $saleDate,
        public readonly ?string   $stockingId = null,
        public readonly ?string   $financialCategoryId = null,
        public readonly SaleStatus $status = SaleStatus::PENDING,
        public readonly ?string   $notes = null,
        public readonly bool      $isHarvestTotal = false,
        public readonly bool      $requiresInvoice = false,
    ) {
    }

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
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
            stockingId:          isset($data['stocking_id']) ? (string) $data['stocking_id']
                               : (isset($data['stockingId']) ? (string) $data['stockingId'] : null),
            financialCategoryId: isset($data['financial_category_id']) ? (string) $data['financial_category_id']
                               : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            status:              isset($data['status'])
                               ? SaleStatus::from((string) $data['status'])
                               : SaleStatus::PENDING,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            isHarvestTotal:      (bool) ($data['is_total_harvest'] ?? $data['isHarvestTotal'] ?? false),
            requiresInvoice:     (bool) ($data['requires_invoice'] ?? $data['requiresInvoice'] ?? false),
        );
    }
}
