<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class TransferInputDTO
{
    public function __construct(
        public string $companyId,
        public string $batchId,
        public string $originTankId,
        public string $destinationTankId,
        public int $quantity,
        public string $description,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:         (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            batchId:           (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            originTankId:      (string) ($data['origin_tank_id'] ?? $data['originTankId'] ?? ''),
            destinationTankId: (string) ($data['destination_tank_id'] ?? $data['destinationTankId'] ?? ''),
            quantity:          (int)    ($data['quantity'] ?? 0),
            description:       (string) ($data['description'] ?? ''),
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return [
            'company_id'          => $this->companyId,
            'batch_id'            => $this->batchId,
            'origin_tank_id'      => $this->originTankId,
            'destination_tank_id' => $this->destinationTankId,
            'quantity'            => $this->quantity,
            'description'         => $this->description,
        ];
    }
}
