<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class AlertDTO
{
    /**
     * @param  array{name?: string|null}|null  $company
     */
    public function __construct(
        public string $id,
        public string $alertType,
        public string $message,
        public string $status,
        public string $createdAt,
        public ?array $company = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            alertType: $data['alert_type'],
            message: $data['message'],
            status: $data['status'],
            createdAt: $data['created_at'],
            company: isset($data['company']) ? ['name' => $data['company']['name'] ?? null] : null,
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
            'alertType' => $this->alertType,
            'message'   => $this->message,
            'status'    => $this->status,
            'company'   => $this->company,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->alertType === '' || $this->status === '';
    }
}
