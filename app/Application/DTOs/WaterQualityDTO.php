<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class WaterQualityDTO
{
    public function __construct(
        public readonly string  $tankId,
        public readonly string  $companyId,
        public readonly string  $measuredAt,
        public readonly ?float  $ph              = null,
        public readonly ?float  $dissolvedOxygen = null,
        public readonly ?float  $temperature     = null,
        public readonly ?float  $ammonia         = null,
        public readonly ?float  $salinity        = null,
        public readonly ?float  $turbidity       = null,
        public readonly ?string $notes           = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tankId:          (string) ($data['tank_id']          ?? $data['tankId']          ?? ''),
            companyId:       (string) ($data['company_id']       ?? $data['companyId']       ?? ''),
            measuredAt:      (string) ($data['measured_at']      ?? $data['measuredAt']      ?? ''),
            ph:              isset($data['ph'])               ? (float) $data['ph']               : null,
            dissolvedOxygen: isset($data['dissolved_oxygen'])
                ? (float) $data['dissolved_oxygen']
                : (isset($data['dissolvedOxygen']) ? (float) $data['dissolvedOxygen'] : null),
            temperature:     isset($data['temperature'])     ? (float) $data['temperature']     : null,
            ammonia:         isset($data['ammonia'])         ? (float) $data['ammonia']         : null,
            salinity:        isset($data['salinity'])        ? (float) $data['salinity']        : null,
            turbidity:       isset($data['turbidity'])       ? (float) $data['turbidity']       : null,
            notes:           isset($data['notes'])           ? (string) $data['notes']          : null,
        );
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
        ], static fn ($v): bool => $v !== null);
    }
}
