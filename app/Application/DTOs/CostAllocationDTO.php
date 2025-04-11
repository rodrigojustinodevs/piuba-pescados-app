<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class CostAllocationDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $description,
        public float $amount,
        public string $registrationDate,
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
            description: $data['description'],
            amount: (float) $data['amount'],
            registrationDate: $data['registration_date'],
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
            'description'      => $this->description,
            'amount'           => $this->amount,
            'registrationDate' => $this->registrationDate,
            'company'          => $this->company,
            'createdAt'        => $this->createdAt,
            'updatedAt'        => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
            ($this->description === '' || $this->description === '0') &&
            $this->amount === 0.0 &&
            ($this->registrationDate === '' || $this->registrationDate === '0');
    }
}
