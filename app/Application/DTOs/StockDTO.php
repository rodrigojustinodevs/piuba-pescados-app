<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class StockDTO
{
    public function __construct(
        public string $id,
        public string $companyId,
        public string $supplyId,
        public float $currentQuantity,
        public string $unit,
        public float $unitPrice,
        public float $minimumStock,
        public float $withdrawalQuantity,
        public ?string $supplierId = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'companyId'          => $this->companyId,
            'supplyId'           => $this->supplyId,
            'supplierId'         => $this->supplierId,
            'currentQuantity'    => $this->currentQuantity,
            'unit'               => $this->unit,
            'unitPrice'          => $this->unitPrice,
            'minimumStock'       => $this->minimumStock,
            'withdrawalQuantity' => $this->withdrawalQuantity,
            'createdAt'          => $this->createdAt,
            'updatedAt'          => $this->updatedAt,
        ];
    }
}
