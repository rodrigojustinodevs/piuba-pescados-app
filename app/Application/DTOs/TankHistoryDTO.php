<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\TankHistoryEvent;

final readonly class TankHistoryDTO
{
    public function __construct(
        public string $companyId,
        public string $tankId,
        public TankHistoryEvent $event,
        public string $eventDate,
        public ?string $description = null,
        public ?string $performedBy = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:   (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            tankId:      (string) ($data['tank_id'] ?? $data['tankId'] ?? ''),
            event:       TankHistoryEvent::from((string) ($data['event'] ?? '')),
            eventDate:   (string) ($data['event_date'] ?? $data['eventDate'] ?? ''),
            description: isset($data['description']) ? (string) $data['description'] : null,
            performedBy: isset($data['performed_by'])
                ? (string) $data['performed_by']
                : (isset($data['performedBy']) ? (string) $data['performedBy'] : null),
        );
    }
}
