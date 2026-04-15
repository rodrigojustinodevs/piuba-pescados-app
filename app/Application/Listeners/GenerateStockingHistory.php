<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Events\FeedingCreated;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Events\SaleProcessed;
use App\Domain\Models\Stocking;
use App\Domain\Models\StockingHistory;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Listener que cria registros de histórico do stocking para os três eventos de domínio.
 *
 * Mudança em relação à versão anterior:
 *  - findActiveStockingByBatch() usava Stocking::query() diretamente.
 *    Agora usa StockingRepositoryInterface::findByBatchOrFail() — mantém
 *    a camada de infraestrutura encapsulada e facilita testes.
 *
 * Registrado para: FeedingCreated | MortalityRecorded | SaleProcessed
 */
final readonly class GenerateStockingHistory
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    public function handleFeedingCreated(FeedingCreated $event): void
    {
        $stocking = $this->stockingRepository->findByBatchOrFail($event->feeding->batch_id);

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
        $stocking = $this->stockingRepository->findByBatchOrFail($event->mortality->batch_id);

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
}
