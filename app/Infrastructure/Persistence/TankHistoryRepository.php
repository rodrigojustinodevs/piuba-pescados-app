<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\TankHistoryDTO;
use App\Domain\Enums\TankHistoryEvent;
use App\Domain\Models\TankHistory;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankHistoryRepositoryInterface;

final class TankHistoryRepository implements TankHistoryRepositoryInterface
{
    public function create(TankHistoryDTO $dto): TankHistory
    {
        /** @var TankHistory $history */
        $history = TankHistory::create([
            'company_id'   => $dto->companyId,
            'tank_id'      => $dto->tankId,
            'event'        => $dto->event->value,
            'event_date'   => $dto->eventDate,
            'description'  => $dto->description,
            'performed_by' => $dto->performedBy,
        ]);

        return $history->load(['tank:id,name,status', 'company:id,name']);
    }

    public function findOrFail(string $id): TankHistory
    {
        return TankHistory::with(['tank:id,name,status', 'company:id,name'])
            ->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     tank_id?: string|null,
     *     event?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = TankHistory::with(['tank:id,name,status'])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['tank_id']),
                static fn ($q) => $q->where('tank_id', $filters['tank_id']),
            )
            ->when(
                ! empty($filters['event']),
                static fn ($q) => $q->where(
                    'event',
                    TankHistoryEvent::from($filters['event'])->value,
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
