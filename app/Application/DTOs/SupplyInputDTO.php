<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;

final readonly class SupplyInputDTO
{
    public function __construct(
        public string $companyId,
        public string $name,
        public SupplyCategoryEnum $category,
        public string $unit,
        public float $unitCost,
        public float $salePrice,
        public float $currentStock,
        public float $minStock,
        public ?string $supplierId,
        public bool $isProduct,
        public SupplyStatusEnum $status,
        public ?string $description,
        public ?string $sku,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $category = $data['category'] ?? SupplyCategoryEnum::OTHER->value;
        $status   = $data['status'] ?? SupplyStatusEnum::ACTIVE->value;

        return new self(
            companyId:  (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            name:       (string) ($data['name'] ?? ''),
            category:   $category instanceof SupplyCategoryEnum
                ? $category
                : SupplyCategoryEnum::from((string) $category),
            unit:       (string) ($data['unit'] ?? 'kg'),
            unitCost:   (float) ($data['unit_cost'] ?? $data['unitCost'] ?? 0),
            salePrice:  (float) ($data['sale_price'] ?? $data['salePrice'] ?? 0),
            currentStock: (float) ($data['current_stock'] ?? $data['currentStock'] ?? 0),
            minStock:   (float) ($data['min_stock'] ?? $data['minStock'] ?? 0),
            supplierId: isset($data['supplier_id'])
                ? (string) $data['supplier_id']
                : (isset($data['supplierId']) ? (string) $data['supplierId'] : null),
            isProduct:  (bool) ($data['is_product'] ?? $data['isProduct'] ?? false),
            status:     $status instanceof SupplyStatusEnum
                ? $status
                : SupplyStatusEnum::from((string) $status),
            description: isset($data['description']) ? (string) $data['description'] : null,
            sku:        isset($data['sku']) ? (string) $data['sku'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'company_id'    => $this->companyId,
            'sku'           => $this->sku,
            'name'          => $this->name,
            'category'      => $this->category->value,
            'unit'          => $this->unit,
            'unit_cost'     => $this->unitCost,
            'sale_price'    => $this->salePrice,
            'current_stock' => $this->currentStock,
            'min_stock'     => $this->minStock,
            'supplier_id'   => $this->supplierId,
            'is_product'    => $this->isProduct,
            'status'        => $this->status->value,
            'description'   => $this->description,
        ];
    }
}
