<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class StockDTO
{
    /**
     * @param array{name?: string|null}|null $company
     * @param array{id?: string|null, name?: string|null}|null $supplier
     */
    public function __construct(
        public string $id,
        public float $currentQuantity,
        public string $unit,
        public float $unitPrice,
        public float $minimumStock,
        public float $withdrawalQuantity,
        public ?array $company = null,
        public ?array $supplier = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            currentQuantity: (float) $data['current_quantity'],
            unit: $data['unit'],
            unitPrice: (float) ($data['unit_price'] ?? 0),
            minimumStock: (float) $data['minimum_stock'],
            withdrawalQuantity: (float) $data['withdrawal_quantity'],
            company: isset($data['company']) ? [
                'name' => $data['company']['name'] ?? null,
            ] : null,
            supplier: isset($data['supplier']) ? [
                'id'   => $data['supplier']['id'] ?? null,
                'name' => $data['supplier']['name'] ?? null,
            ] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'currentQuantity'   => $this->currentQuantity,
            'unit'              => $this->unit,
            'unitPrice'         => $this->unitPrice,
            'minimumStock'      => $this->minimumStock,
            'withdrawalQuantity' => $this->withdrawalQuantity,
            'company'           => $this->company,
            'supplier'          => $this->supplier,
            'createdAt'         => $this->createdAt,
            'updatedAt'         => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               $this->currentQuantity === 0.0 &&
               ($this->unit === '' || $this->unit === '0') &&
               $this->unitPrice === 0.0 &&
               $this->minimumStock === 0.0 &&
               $this->withdrawalQuantity === 0.0;
    }
}
