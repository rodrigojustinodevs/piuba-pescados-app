<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Enums\StockingStatus;
use App\Domain\Events\FeedingCreated;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Events\SaleProcessed;
use App\Domain\Models\Stocking;
use App\Domain\Models\StockingHistory;
use Illuminate\Support\Str;

/**
 * Single listener that handles all three domain events and creates the
 * corresponding StockingHistory records automatically.
 *
 * Registered for: FeedingCreated | MortalityRecorded | SaleProcessed
 */
final class GenerateStockingHistory
{
    public function handleFeedingCreated(FeedingCreated $event): void
    {
        $stocking = $this->findActiveStockingByBatch($event->feeding->batch_id);

        if (! $stocking instanceof Stocking) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $event->companyId,
            'stocking_id' => $stocking->id,
            'event'       => StockingHistoryEvent::FEEDING->value,
            'event_date'  => $event->feeding->feeding_date?->toDateString() ?? now()->toDateString(),
            'notes'       => sprintf(
                'Alimentação: %.2f kg de %s fornecidos.',
                $event->feeding->quantity_provided,
                $event->feeding->feed_type,
            ),
        ]);
    }

    public function handleMortalityRecorded(MortalityRecorded $event): void
    {
        $stocking = $this->findActiveStockingByBatch($event->mortality->batch_id);

        if (! $stocking instanceof Stocking) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $event->companyId,
            'stocking_id' => $stocking->id,
            'event'       => StockingHistoryEvent::MORTALITY->value,
            'event_date'  => $event->mortality->mortality_date?->toDateString() ?? now()->toDateString(),
            'quantity'    => $event->mortality->quantity,
            'notes'       => sprintf(
                'Mortalidade registrada: %d unidades. Causa: %s.',
                $event->mortality->quantity,
                $event->mortality->cause,
            ),
        ]);
    }

    public function handleSaleProcessed(SaleProcessed $event): void
    {
        $sale = $event->sale;

        // Only generate stocking history when the sale is linked to a specific stocking
        if ($sale->stocking_id === null) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $sale->company_id,
            'stocking_id' => $sale->stocking_id,
            'event'       => StockingHistoryEvent::HARVEST->value,
            'event_date'  => $sale->sale_date->toDateString(),
            'notes'       => sprintf(
                'Despesca: %.2f kg a R$ %.2f/kg. Receita total: R$ %.2f.',
                $sale->total_weight,
                $sale->price_per_kg,
                $sale->total_revenue,
            ),
        ]);
    }

    /**
     * Finds the latest active stocking for a given batch.
     * Returns null if no active stocking exists (feeding/mortality without stocking context).
     */
    private function findActiveStockingByBatch(string $batchId): ?Stocking
    {
        return Stocking::where('batch_id', $batchId)
            ->where('status', StockingStatus::ACTIVE)
            ->latest('stocking_date')
            ->first();
    }
}
