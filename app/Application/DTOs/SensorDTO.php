<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SensorType;
use App\Domain\Enums\Status;

class SensorDTO
{
    /**
     * @param array{id?: string|null, name?: string|null}|null $tank
     */
    public function __construct(
        public string $id,
        public SensorType $sensorType,
        public Status $status,
        public ?array $tank = null,
        public ?string $installationDate = null,
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
            sensorType: SensorType::from($data['sensor_type']),
            status: Status::from($data['status']),
            tank: isset($data['tank']) ? [
                'id'   => $data['tank']['id'] ?? null,
                'name' => $data['tank']['name'] ?? null,
            ] : null,
            installationDate: $data['installation_date'] ?? null,
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
            'sensorType'       => $this->sensorType->value,
            'status'           => $this->status->value,
            'tank'             => $this->tank,
            'installationDate' => $this->installationDate,
            'createdAt'        => $this->createdAt,
            'updatedAt'        => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->id === '0';
    }
}
