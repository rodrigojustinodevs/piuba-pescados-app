<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\Sensor;

final readonly class SensorDTO
{
    /**
     * @param array{id: string, name: string}|null $tank
     */
    public function __construct(
        public string $id,
        public string $tankId,
        public string $companyId,
        public string $sensorType,
        public string $status,
        public ?string $installationDate = null,
        public ?string $notes = null,
        public ?array $tank = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromModel(Sensor $sensor): self
    {
        $installationStr = $sensor->installation_date?->toDateString();

        $tank = $sensor->relationLoaded('tank') && $sensor->tank !== null
            ? ['id' => $sensor->tank->id, 'name' => $sensor->tank->name]
            : ['id' => '', 'name' => ''];

        return new self(
            id:                 $sensor->id,
            tankId:             $sensor->tank_id,
            companyId:          $sensor->company_id,
            sensorType:         $sensor->sensor_type,
            status:             $sensor->status,
            installationDate:   $installationStr,
            notes:              $sensor->notes,
            tank:               $tank,
            createdAt: $sensor->created_at?->toDateTimeString(),
            updatedAt: $sensor->updated_at?->toDateTimeString(),
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id:                 (string) ($data['id'] ?? ''),
            tankId:             (string) ($data['tank_id'] ?? $data['tankId'] ?? ''),
            companyId:          (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            sensorType:         (string) ($data['sensor_type'] ?? $data['sensorType'] ?? ''),
            status:             (string) ($data['status'] ?? 'active'),
            installationDate: isset($data['installation_date'])
                ? (string) $data['installation_date']
                : (isset($data['installationDate']) ? (string) $data['installationDate'] : null),
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'tankId'           => $this->tankId,
            'companyId'        => $this->companyId,
            'sensorType'       => $this->sensorType,
            'status'           => $this->status,
            'installationDate' => $this->installationDate,
            'notes'            => $this->notes,
            'tank'             => $this->tank ?? ['id' => '', 'name' => ''],
            'createdAt'        => $this->createdAt,
            'updatedAt'        => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '';
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'tank_id'           => $this->tankId,
            'company_id'        => $this->companyId,
            'sensor_type'       => $this->sensorType,
            'status'            => $this->status,
            'installation_date' => $this->installationDate,
            'notes'             => $this->notes,
        ], static fn (?string $v): bool => $v !== null);
    }
}
