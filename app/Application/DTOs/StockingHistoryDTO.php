<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockingHistoryEvent;

final readonly class StockingHistoryDTO
{
    public function __construct(
        public string $companyId,
        public string $stockingId,
        public StockingHistoryEvent $event,
        public string $eventDate,
        public ?int $quantity = null,
        public ?float $averageWeight = null,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:    (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            stockingId:   (string) ($data['stocking_id'] ?? $data['stockingId'] ?? ''),
            event:        StockingHistoryEvent::from((string) ($data['event'] ?? '')),
            eventDate:    (string) ($data['event_date'] ?? $data['eventDate'] ?? ''),
            quantity:     isset($data['quantity']) ? (int)   $data['quantity'] : null,
            averageWeight: isset($data['average_weight'])
                ? (float) $data['average_weight']
                : (isset($data['averageWeight']) ? (float) $data['averageWeight'] : null),
            notes:        isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
