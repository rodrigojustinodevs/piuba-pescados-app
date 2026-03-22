<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class StockDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $companyId,
        public readonly string  $supplyId,
        public readonly float   $currentQuantity,
        public readonly string  $unit,
        public readonly float   $unitPrice,
        public readonly float   $minimumStock,
        public readonly float   $withdrawalQuantity,
        public readonly ?string $supplierId = null,
        public readonly ?string $createdAt  = null,
        public readonly ?string $updatedAt  = null,
    ) {}

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