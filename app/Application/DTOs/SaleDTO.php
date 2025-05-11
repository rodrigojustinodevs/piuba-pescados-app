<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SaleDTO
{
    /**
     * @param  array{name?: string|null}|null  $company
     * @param  array{name?: string|null}|null  $client
     */
    public function __construct(
        public string $id,
        public float $totalWeight,
        public float $pricePerKg,
        public float $totalRevenue,
        public string $saleDate,
        public ?array $company,
        public ?array $client,
        public string $batcheId,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            totalWeight: $data['total_weight'],
            pricePerKg: $data['price_per_kg'],
            totalRevenue: $data['total_revenue'],
            saleDate: $data['sale_date'],
            company: isset($data['company']) ? ['name' => $data['company']['name'] ?? null] : null,
            client: isset($data['client']) ? [
                'id'   => $data['client']['id'] ?? null,
                'name' => $data['client']['name'] ?? null,
            ] : null,
            batcheId: $data['batche_id'],
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
            'totalWeight'  => $this->totalWeight,
            'pricePerKg'   => $this->pricePerKg,
            'totalRevenue' => $this->totalRevenue,
            'saleDate'     => $this->saleDate,
            'company'      => $this->company,
            'client'       => $this->client,
            'batcheId'     => $this->batcheId,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->totalWeight <= 0 || $this->pricePerKg <= 0;
    }
}
