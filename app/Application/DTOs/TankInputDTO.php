<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\Status;
use App\Domain\ValueObjects\CapacityLiters;
use App\Domain\ValueObjects\Location;
use App\Domain\ValueObjects\Name;

final readonly class TankInputDTO
{
    public function __construct(
        public ?string $name,
        public ?int $capacityLiters,
        public ?string $location,
        public ?string $tankTypeId,
        public ?string $companyId,
        public Status $status = Status::ACTIVE,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name:           isset($data['name']) ? (new Name($data['name']))->value() : null,
            capacityLiters: ($data['capacityLiters'] ?? $data['capacity_liters'] ?? null) !== null
                ? CapacityLiters::fromInt((int) ($data['capacityLiters'] ?? $data['capacity_liters']))->value()
                : null,
            location:       isset($data['location']) ? (new Location($data['location']))->value() : null,
            tankTypeId:     (string) ($data['tank_type_id'] ?? $data['tankTypeId'] ?? ''),
            companyId:      (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            status:         Status::from((string) ($data['status'] ?? Status::ACTIVE->value)),
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'name'            => $this->name,
            'capacity_liters' => $this->capacityLiters,
            'location'        => $this->location,
            'tank_type_id'    => $this->tankTypeId,
            'company_id'      => $this->companyId,
            'status'          => $this->status,
        ], static fn (int | string | null $v): bool => $v !== null);
    }
}
