<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

final readonly class SaleInputDTO
{
    public function __construct(
        public string $companyId,
        public string $clientId,
        public string $batchId,
        public float $totalWeight,
        public float $pricePerKg,
        public string $saleDate,
        public ?string $stockingId = null,
        public ?string $financialCategoryId = null,
        public SaleStatus $status = SaleStatus::PENDING,
        public ?string $notes = null,
    ) {
    }

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:            (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            batchId:             (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            totalWeight:         (float) ($data['total_weight'] ?? $data['totalWeight'] ?? 0),
            pricePerKg:          (float) ($data['price_per_kg'] ?? $data['pricePerKg'] ?? 0),
            saleDate:            (string) ($data['sale_date'] ?? $data['saleDate'] ?? ''),
            stockingId:          isset($data['stocking_id']) ? (string) $data['stocking_id']
                               : (isset($data['stockingId']) ? (string) $data['stockingId'] : null),
            financialCategoryId: isset($data['financial_category_id']) ? (string) $data['financial_category_id']
                               : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            status:              isset($data['status'])
                               ? SaleStatus::from((string) $data['status'])
                               : SaleStatus::PENDING,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
