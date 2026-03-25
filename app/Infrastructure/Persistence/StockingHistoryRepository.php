<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\StockingHistoryDTO;
use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Models\StockingHistory;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingHistoryRepositoryInterface;

final class StockingHistoryRepository implements StockingHistoryRepositoryInterface
{
    public function create(StockingHistoryDTO $dto): StockingHistory
    {
        /** @var StockingHistory $history */
        $history = StockingHistory::create([
            'company_id'     => $dto->companyId,
            'stocking_id'    => $dto->stockingId,
            'event'          => $dto->event->value,
            'event_date'     => $dto->eventDate,
            'quantity'       => $dto->quantity,
            'average_weight' => $dto->averageWeight,
            'notes'          => $dto->notes,
        ]);

        return $history->load(['stocking:id,batch_id,current_quantity,average_weight,estimated_biomass,status']);
    }

    public function findOrFail(string $id): StockingHistory
    {
        return StockingHistory::with([
            'stocking:id,batch_id,current_quantity,average_weight,estimated_biomass,status',
        ])->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     stocking_id?: string|null,
     *     event?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = StockingHistory::with([
            'stocking:id,batch_id,current_quantity,average_weight,estimated_biomass,status',
        ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['stocking_id']),
                static fn ($q) => $q->where('stocking_id', $filters['stocking_id']),
            )
            ->when(
                ! empty($filters['event']),
                static fn ($q) => $q->where(
                    'event',
                    StockingHistoryEvent::from($filters['event'])->value,
                ),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('event_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('event_date', '<=', $filters['date_to']),
            )
            ->latest('event_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}
