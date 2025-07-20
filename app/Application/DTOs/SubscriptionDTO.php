<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SubscriptionDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $plan,
        public string $status,
        public string $startDate,
        public string $endDate,
        public ?array $company = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            plan: $data['plan'],
            status: $data['status'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            company: isset($data['company']) ? ['name' => $data['company']['name'] ?? null] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'plan'      => $this->plan,
            'status'    => $this->status,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
            'company'   => $this->company,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->plan === '' || $this->status === '';
    }
}
