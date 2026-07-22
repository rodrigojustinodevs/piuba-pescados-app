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
        public ?string $name = null,
        public ?string $serialNumber = null,
        public ?int $battery = null,
        public ?string $unit = null,
        public ?float $lastReading = null,
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
            status:             self::toOutputStatus((string) $sensor->status),
            name:               $sensor->name,
            serialNumber:       $sensor->serial_number,
            battery:            $sensor->battery,
            unit:               $sensor->unit,
            lastReading:        $sensor->last_reading,
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
            status:             self::toPersistenceStatus((string) ($data['status'] ?? 'active')),
            name: isset($data['name']) ? (string) $data['name'] : null,
            serialNumber: isset($data['serial_number'])
                ? (string) $data['serial_number']
                : (isset($data['serialNumber']) ? (string) $data['serialNumber'] : null),
            battery: isset($data['battery']) ? (int) $data['battery'] : null,
            unit: isset($data['unit']) ? (string) $data['unit'] : null,
            lastReading: isset($data['last_reading'])
                ? (float) $data['last_reading']
                : (isset($data['lastReading']) ? (float) $data['lastReading'] : null),
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
            'name'             => $this->name,
            'serialNumber'     => $this->serialNumber,
            'battery'          => $this->battery,
            'unit'             => $this->unit,
            'lastReading'      => $this->lastReading,
            'status'           => self::toOutputStatus($this->status),
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
            'name'              => $this->name,
            'serial_number'     => $this->serialNumber,
            'battery'           => $this->battery,
            'unit'              => $this->unit,
            'last_reading'      => $this->lastReading,
            'status'            => self::toPersistenceStatus($this->status),
            'installation_date' => $this->installationDate,
            'notes'             => $this->notes,
        ], static fn (mixed $v): bool => $v !== null);
    }

    public static function toPersistenceStatus(string $status): string
    {
        return match (mb_strtolower(trim($status))) {
            'online', 'ativo'    => 'active',
            'offline', 'inativo' => 'inactive',
            default              => $status,
        };
    }

    public static function toOutputStatus(string $status): string
    {
        return match (mb_strtolower(trim($status))) {
            'active', 'ativo'     => 'Online',
            'inactive', 'inativo' => 'Offline',
            default               => $status,
        };
    }
}
