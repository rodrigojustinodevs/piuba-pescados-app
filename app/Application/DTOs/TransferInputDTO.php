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
        public string $transferDate,
        public string $status,
        public string $reason,
        public string $responsible,
        public ?float $averageWeight,
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
            quantity:          (int) ($data['quantity'] ?? 0),
            description:       (string) ($data['description'] ?? ''),
            transferDate:      (string) ($data['transfer_date'] ?? $data['transferDate'] ?? ''),
            status:            (string) ($data['status'] ?? ''),
            reason:            (string) ($data['reason'] ?? ''),
            responsible:       (string) ($data['responsible'] ?? ''),
            averageWeight:     isset($data['average_weight']) || isset($data['averageWeight'])
                ? (float) ($data['average_weight'] ?? $data['averageWeight'])
                : null,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return [
            'batch_id'            => $this->batchId,
            'origin_tank_id'      => $this->originTankId,
            'destination_tank_id' => $this->destinationTankId,
            'quantity'            => $this->quantity,
            'description'         => $this->description,
            'transfer_date'       => $this->transferDate,
            'status'              => $this->status,
            'reason'              => $this->reason,
            'responsible'         => $this->responsible,
            'average_weight'      => $this->averageWeight,
        ];
    }
}
