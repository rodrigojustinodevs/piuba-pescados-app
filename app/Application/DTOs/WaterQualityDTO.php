<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\WaterQuality;
use Carbon\CarbonInterface;

final readonly class WaterQualityDTO
{
    /**
     * @param array{id: string, name: string}|null $tank
     */
    public function __construct(
        public string $id,
        public string $tankId,
        public string $companyId,
        public string $measuredAt,
        public ?float $ph = null,
        public ?float $dissolvedOxygen = null,
        public ?float $temperature = null,
        public ?float $ammonia = null,
        public ?float $salinity = null,
        public ?float $turbidity = null,
        public ?string $notes = null,
        public ?array $tank = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromModel(WaterQuality $quality): self
    {
        $measured    = $quality->measured_at;
        $measuredStr = $measured instanceof CarbonInterface
            ? $measured->toDateString()
            : (string) $measured;

        $companyId   = '';
        $tankPayload = ['id' => '', 'name' => ''];

        if ($quality->relationLoaded('tank') && $quality->tank !== null) {
            $companyId   = (string) $quality->tank->company_id;
            $tankPayload = ['id' => $quality->tank->id, 'name' => $quality->tank->name];
        }

        return new self(
            id:              $quality->id,
            tankId:          $quality->tank_id,
            companyId:       $companyId,
            measuredAt:      $measuredStr,
            ph:              (float) $quality->ph,
            dissolvedOxygen: (float) $quality->dissolved_oxygen,
            temperature:     (float) $quality->temperature,
            ammonia:         (float) $quality->ammonia,
            salinity:        (float) $quality->salinity,
            turbidity:       (float) $quality->turbidity,
            notes:           (string) $quality->notes,
            tank:            $tankPayload,
            createdAt: $quality->created_at?->toDateTimeString(),
            updatedAt: $quality->updated_at?->toDateTimeString(),
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id:         (string) ($data['id'] ?? ''),
            tankId:     (string) ($data['tank_id'] ?? $data['tankId'] ?? ''),
            companyId:  (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            measuredAt: (string) ($data['measured_at'] ?? $data['measuredAt'] ?? ''),
            ph:              isset($data['ph']) ? (float) $data['ph'] : null,
            dissolvedOxygen: isset($data['dissolved_oxygen'])
                ? (float) $data['dissolved_oxygen']
                : (isset($data['dissolvedOxygen']) ? (float) $data['dissolvedOxygen'] : null),
            temperature: isset($data['temperature']) ? (float) $data['temperature'] : null,
            ammonia:     isset($data['ammonia']) ? (float) $data['ammonia'] : null,
            salinity:    isset($data['salinity']) ? (float) $data['salinity'] : null,
            turbidity:   isset($data['turbidity']) ? (float) $data['turbidity'] : null,
            notes:       isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'tankId'          => $this->tankId,
            'companyId'       => $this->companyId,
            'measuredAt'      => $this->measuredAt,
            'ph'              => $this->ph,
            'dissolvedOxygen' => $this->dissolvedOxygen,
            'temperature'     => $this->temperature,
            'ammonia'         => $this->ammonia,
            'salinity'        => $this->salinity,
            'turbidity'       => $this->turbidity,
            'notes'           => $this->notes,
            'tank'            => $this->tank ?? ['id' => '', 'name' => ''],
            'createdAt'       => $this->createdAt,
            'updatedAt'       => $this->updatedAt,
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
            'tank_id'          => $this->tankId,
            'company_id'       => $this->companyId,
            'measured_at'      => $this->measuredAt,
            'ph'               => $this->ph,
            'dissolved_oxygen' => $this->dissolvedOxygen,
            'temperature'      => $this->temperature,
            'ammonia'          => $this->ammonia,
            'salinity'         => $this->salinity,
            'turbidity'        => $this->turbidity,
            'notes'            => $this->notes,
        ], static fn (float | string | null $v): bool => $v !== null);
    }
}
