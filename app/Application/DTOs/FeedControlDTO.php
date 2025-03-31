<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class FeedControlDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $feedType,
        public float $currentStock,
        public float $minimumStock,
        public float $dailyConsumption,
        public float $totalConsumption,
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
            feedType: $data['feed_type'],
            currentStock: (float) $data['current_stock'],
            minimumStock: (float) $data['minimum_stock'],
            dailyConsumption: (float) $data['daily_consumption'],
            totalConsumption: (float) $data['total_consumption'],
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
            'id'               => $this->id,
            'feedType'         => $this->feedType,
            'currentStock'     => $this->currentStock,
            'minimumStock'     => $this->minimumStock,
            'dailyConsumption' => $this->dailyConsumption,
            'totalConsumption' => $this->totalConsumption,
            'company'          => $this->company,
            'createdAt'        => $this->createdAt,
            'updatedAt'        => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->feedType === '' || $this->feedType === '0') &&
               $this->currentStock === 0.0 &&
               $this->minimumStock === 0.0 &&
               $this->dailyConsumption === 0.0 &&
               $this->totalConsumption === 0.0;
    }
}
