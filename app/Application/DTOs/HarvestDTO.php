<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * DTO de resposta do módulo de colheita (Harvest).
 */
final readonly class HarvestDTO
{
    /**
     * @param array<int, array{class: string, quantity: int, averageWeight: float, pricePerKg: float}> $sizeClassifications
     */
    public function __construct(
        public string  $id,
        public string  $batchId,
        public ?string $tankId,
        public string  $harvestDate,
        public string  $type,
        public string  $status,
        public ?string $destination,
        public int     $initialPopulation,
        public int     $harvestedQuantity,
        public float   $averageWeight,
        public float   $totalWeight,
        public float   $pricePerKg,
        public float   $totalRevenue,
        public float   $operationalCost,
        public float   $netProfit,
        public float   $survivalRate,
        public ?string $clientDestination,
        public ?string $responsible,
        public ?string $notes,
        public array   $sizeClassifications = [],
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->id === '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'batchId'             => $this->batchId,
            'tankId'              => $this->tankId,
            'harvestDate'         => $this->harvestDate,
            'type'                => $this->type,
            'status'              => $this->status,
            'destination'         => $this->destination,
            'initialPopulation'   => $this->initialPopulation,
            'harvestedQuantity'   => $this->harvestedQuantity,
            'averageWeight'       => $this->averageWeight,
            'totalWeight'         => $this->totalWeight,
            'pricePerKg'          => $this->pricePerKg,
            'totalRevenue'        => $this->totalRevenue,
            'operationalCost'     => $this->operationalCost,
            'netProfit'           => $this->netProfit,
            'survivalRate'        => $this->survivalRate,
            'clientDestination'   => $this->clientDestination,
            'responsible'         => $this->responsible,
            'notes'               => $this->notes,
            'sizeClassifications' => $this->sizeClassifications,
            'createdAt'           => $this->createdAt,
            'updatedAt'           => $this->updatedAt,
        ];
    }
}
