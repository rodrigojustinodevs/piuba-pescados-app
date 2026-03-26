<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class FeedingInputDTO
{
    public function __construct(
        public string $batchId,
        public string $feedingDate,
        public float $quantityProvided,
        public string $feedType,
        public ?string $stockId = null,
        public float $stockReductionQuantity = 0.0,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            batchId:                (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            feedingDate:            (string) ($data['feeding_date'] ?? $data['feedingDate'] ?? ''),
            quantityProvided:       (float) ($data['quantity_provided'] ?? $data['quantityProvided'] ?? 0),
            feedType:               (string) ($data['feed_type'] ?? $data['feedType'] ?? ''),
            stockId:                isset($data['stock_id']) ? (string) $data['stock_id']
                                  : (isset($data['stockId']) ? (string) $data['stockId'] : null),
            stockReductionQuantity: (float) ($data['stock_reduction_quantity'] ?? $data['stockReductionQuantity'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'batch_id'                 => $this->batchId,
            'feeding_date'             => $this->feedingDate,
            'quantity_provided'        => $this->quantityProvided,
            'feed_type'                => $this->feedType,
            'stock_id'                 => $this->stockId,
            'stock_reduction_quantity' => $this->stockReductionQuantity,
        ];
    }
}
