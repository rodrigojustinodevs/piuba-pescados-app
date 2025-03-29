<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class StockDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $supplyName,
        public float $currentQuantity,
        public string $unit,
        public float $minimumStock,
        public float $withdrawnQuantity,
        public ?array $company = null,
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
            supplyName: $data['supply_name'],
            currentQuantity: (float) $data['current_quantity'],
            unit: $data['unit'],
            minimumStock: (float) $data['minimum_stock'],
            withdrawnQuantity: (float) $data['withdrawn_quantity'],
            company: isset($data['company']) ? [
                'name' => $data['company']['name'] ?? null,
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
            'supplyName'        => $this->supplyName,
            'currentQuantity'   => $this->currentQuantity,
            'unit'              => $this->unit,
            'minimumStock'      => $this->minimumStock,
            'withdrawnQuantity' => $this->withdrawnQuantity,
            'company'           => $this->company,
            'createdAt'         => $this->createdAt,
            'updatedAt'         => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->supplyName === '' || $this->supplyName === '0') &&
               $this->currentQuantity === 0.0 &&
               ($this->unit === '' || $this->unit === '0') &&
               $this->minimumStock === 0.0 &&
               $this->withdrawnQuantity === 0.0;
    }
}
