<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialType;

class FinancialTransactionDTO
{
    /**
     * @param array{name?: string|null}|null $company
     * @param array{name?: string|null}|null $category
     */
    public function __construct(
        public string $id,
        public FinancialType $type,
        public string $description,
        public float $amount,
        public string $transactionDate,
        public ?array $company = null,
        public ?array $category = null,
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
            type: FinancialType::from($data['type']),
            description: $data['description'],
            amount: (float) $data['amount'],
            transactionDate: $data['transaction_date'],
            company: isset($data['company']) ? [
                'name' => $data['company']['name'] ?? null,
            ] : null,
            category: isset($data['category']) ? [
                'id'   => $data['category']['id'] ?? '',
                'name' => $data['category']['name'] ?? null,
            ] : null,
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
            'id'              => $this->id,
            'type'            => $this->type->value,
            'description'     => $this->description,
            'amount'          => $this->amount,
            'transactionDate' => $this->transactionDate,
            'company'         => $this->company,
            'category'        => $this->category,
            'createdAt'       => $this->createdAt,
            'updatedAt'       => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
            ($this->description === '' || $this->description === '0');
    }
}
