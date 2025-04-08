<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class PurchaseDTO
{
    /**
     * @param array{id?: string|null, name?: string|null}|null $supplier
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $inputName,
        public float $quantity,
        public float $totalPrice,
        public string $purchaseDate,
        public ?array $supplier = null,
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
            inputName: $data['input_name'],
            quantity: $data['quantity'],
            totalPrice: $data['total_price'],
            purchaseDate: $data['purchase_date'],
            supplier: isset($data['supplier']) ? [
                'id'   => $data['supplier']['id'] ?? null,
                'name' => $data['supplier']['name'] ?? null,
            ] : null,
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
            'id'           => $this->id,
            'inputName'    => $this->inputName,
            'quantity'     => $this->quantity,
            'totalPrice'   => $this->totalPrice,
            'purchaseDate' => $this->purchaseDate,
            'supplier'     => $this->supplier,
            'company'      => $this->company,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->id === '0';
    }
}
