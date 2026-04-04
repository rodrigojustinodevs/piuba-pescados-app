<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

/**
 * DTO do fluxo de despesca/venda.
 *
 * Mudança em relação à versão anterior:
 *   - fromArray() removido: a normalização camel/snake era responsabilidade
 *     da Request. Aqui chega apenas o array validado (snake_case).
 *   - stockingId é string obrigatória no construtor — a guarda de nulidade
 *     saiu do UseCase e virou regra de validação na Request (stocking_id required).
 *   - tolerancePercent removido: o UseCase usa constante interna (50%).
 *     Manter o campo no DTO criava contrato falso.
 */
final readonly class HarvestSaleDTO
{
    public function __construct(
        public string     $companyId,
        public string     $clientId,
        public string     $batchId,
        public string     $stockingId,
        public float      $totalWeight,
        public float      $pricePerKg,
        public string     $saleDate,
        public bool       $isHarvestTotal,
        public SaleStatus $status,
        public ?string    $financialCategoryId = null,
        public ?string    $notes               = null,
        public bool       $needsInvoice        = false,
    ) {}

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /**
     * Constrói o DTO a partir do array validado e normalizado pela SaleStoreRequest.
     * Todas as chaves são snake_case — sem fallback camelCase.
     *
     * @param array<string, mixed> $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            companyId:           (string) ($data['company_id'] ?? ''),
            clientId:            (string)  $data['client_id'],
            batchId:             (string)  $data['batch_id'],
            stockingId:          (string)  $data['stocking_id'],
            totalWeight:         (float)   $data['total_weight'],
            pricePerKg:          (float)   $data['price_per_kg'],
            saleDate:            (string)  $data['sale_date'],
            isHarvestTotal:      (bool)   ($data['is_total_harvest'] ?? false),
            status:              isset($data['status'])
                                     ? SaleStatus::from((string) $data['status'])
                                     : SaleStatus::PENDING,
            financialCategoryId: isset($data['financial_category_id'])
                                     ? (string) $data['financial_category_id']
                                     : null,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            needsInvoice:        (bool) ($data['needs_invoice'] ?? false),
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
            batchId:             $this->batchId,
            totalWeight:         $this->totalWeight,
            pricePerKg:          $this->pricePerKg,
            saleDate:            $this->saleDate,
            stockingId:          $this->stockingId,
            financialCategoryId: $this->financialCategoryId,
            status:              $this->status,
            notes:               $this->notes,
            isHarvestTotal:      $this->isHarvestTotal,
        );
    }
}