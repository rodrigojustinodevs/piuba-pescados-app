<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Events\BatchTransferred;
use App\Domain\Models\StockingHistory;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Cria um registro em stocking_histories quando um lote é transferido entre tanques.
 *
 * Registrado para: BatchTransferred
 */
final readonly class RecordBatchTransferHistory
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    public function handle(BatchTransferred $event): void
    {
        $transfer = $event->transfer;

        $stocking = $this->stockingRepository->findActiveByBatch((string) $transfer->batch_id);

        if (!$stocking instanceof \App\Domain\Models\Stocking) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $event->companyId,
            'stocking_id' => $stocking->id,
            'event'       => StockingHistoryEvent::TRANSFER->value,
            'event_date'  => $transfer->transfer_date?->toDateString() ?? now()->toDateString(),
            'quantity'    => $transfer->quantity,
            'notes'       => sprintf(
                'Transferência de %d unidades do tanque "%s" para "%s". Motivo: %s.',
                $transfer->quantity,
                $transfer->originTank->name ?? (string) $transfer->origin_tank_id,
                $transfer->destinationTank->name ?? (string) $transfer->destination_tank_id,
                $transfer->reason ?? 'other',
            ),
        ]);
    }
}
