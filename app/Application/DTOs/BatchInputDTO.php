<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\ValueObjects\EntryDate;
use App\Domain\ValueObjects\InitialQuantity;
use App\Domain\ValueObjects\Species;

final readonly class BatchInputDTO
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $species,
        public ?int $initialQuantity,
        public ?string $entryDate,
        public ?string $tankId,
        public string $status = 'active',
        public ?string $cultivation = null,
        public ?string $parentGroupId = null,
        public ?float $unitCost = null,
        public ?float $totalCost = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $species         = isset($data['species']) ? (new Species($data['species']))->value() : null;
        $rawQuantity     = $data['initialQuantity'] ?? $data['initial_quantity'] ?? null;
        $initialQuantity = $rawQuantity !== null
            ? InitialQuantity::fromInt((int) $rawQuantity)->value()
            : null;
        $rawEntryDate = $data['entryDate'] ?? $data['entry_date'] ?? null;
        $entryDate    = $rawEntryDate !== null
            ? EntryDate::fromString((string) $rawEntryDate)->toDateString()
            : null;

        return new self(
            name:            $data['name'] ?? null,
            description:     $data['description'] ?? null,
            species:         $species,
            initialQuantity: $initialQuantity,
            entryDate:       $entryDate,
            tankId:          (string) ($data['tank_id'] ?? $data['tankId'] ?? ''),
            status:          $data['status'] ?? 'active',
            cultivation:     $data['cultivation'] ?? null,
            parentGroupId:   $data['parentGroupId'] ?? null,
            unitCost:        $data['unitCost'] ?? null,
            totalCost:       $data['totalCost'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'name'             => $this->name,
            'description'      => $this->description,
            'species'          => $this->species,
            'initial_quantity' => $this->initialQuantity,
            'entry_date'       => $this->entryDate,
            'tank_id'          => $this->tankId,
            'status'           => $this->status,
            'cultivation'      => $this->cultivation,
            'parent_group_id'  => $this->parentGroupId,
            'unit_cost'        => $this->unitCost,
            'total_cost'       => $this->totalCost,
        ], static fn (int | string | null $v): bool => $v !== null);
    }
}
