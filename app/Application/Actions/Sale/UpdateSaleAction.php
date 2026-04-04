<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\SaleStatus;
use App\Domain\Enums\StockingStatus;
use App\Domain\Exceptions\SaleFinanciallyLockedException;
use App\Domain\Models\Batch;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Models\Tank;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class UpdateSaleAction
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly GuardBiomassAction $guardBiomass,
        private readonly CloseStockingAndBatchAction $closeStockingAndBatch,
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly TankRepositoryInterface $tankRepository,
        private readonly FinancialTransactionRepositoryInterface $financialTransactionRepository,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(string $id, array $data): Sale
    {
        return DB::transaction(function () use ($id, $data): Sale {
            /** @var Sale $sale */
            $sale = Sale::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            /** @var Collection<int, FinancialTransaction> $financialTransactions */
            $financialTransactions = FinancialTransaction::query()
                ->where('reference_type', FinancialTransactionReferenceType::SALE->value)
                ->where('reference_id', $sale->id)
                ->lockForUpdate()
                ->get();

            $this->assertAllFinancialTitlesPending($financialTransactions);

            $stocking = null;

            if ($sale->stocking_id !== null) {
                /** @var Stocking $stocking */
                $stocking = Stocking::query()->whereKey($sale->stocking_id)->lockForUpdate()->firstOrFail();
            }

            $attributes = $this->buildAttributes($sale, $data);

            $oldWeight = (float) $sale->total_weight;
            $newWeight = isset($attributes['total_weight'])
                ? (float) $attributes['total_weight']
                : $oldWeight;

            if ($stocking instanceof Stocking && $newWeight > $oldWeight) {
                $this->guardBiomass->execute($stocking, $newWeight, $sale->id);
            }

            $oldHarvest = (bool) $sale->is_total_harvest;
            $newHarvest = array_key_exists('is_total_harvest', $attributes)
                ? (bool) $attributes['is_total_harvest']
                : $oldHarvest;

            if ($stocking instanceof Stocking) {
                if ($oldHarvest && ! $newHarvest) {
                    $this->revertTotalHarvest($sale, $stocking);
                } elseif (! $oldHarvest && $newHarvest && ! $stocking->isClosed()) {
                    $this->closeStockingAndBatch->execute($stocking);
                    $stocking->refresh();
                }
            }

            $newRevenue = $this->resolveTotalRevenue($sale, $attributes);
            $oldRevenue = round((float) $sale->total_revenue, 2);

            if ($financialTransactions->isNotEmpty()
                && abs($newRevenue - $oldRevenue) > 0.000_01) {
                foreach ($financialTransactions as $tx) {
                    $this->financialTransactionRepository->update((string) $tx->id, [
                        'amount' => $newRevenue,
                    ]);
                }
            }

            if ($attributes === []) {
                return $this->saleRepository->findOrFail($id);
            }

            return $this->saleRepository->update($id, $attributes);
        });
    }

    /**
     * @param Collection<int, FinancialTransaction> $transactions
     */
    private function assertAllFinancialTitlesPending(Collection $transactions): void
    {
        foreach ($transactions as $tx) {
            if ($tx->status !== FinancialTransactionStatus::PENDING) {
                throw new SaleFinanciallyLockedException();
            }
        }
    }

    private function revertTotalHarvest(Sale $sale, Stocking $stocking): void
    {
        $this->stockingRepository->update((string) $stocking->id, [
            'status'    => StockingStatus::ACTIVE->value,
            'closed_at' => null,
        ]);
        $stocking->refresh();

        /** @var Batch $batch */
        $batch = Batch::query()->whereKey($sale->batch_id)->lockForUpdate()->firstOrFail();

        if ($batch->isFinished()) {
            $this->batchRepository->update((string) $batch->id, [
                'status' => BatchStatus::ACTIVE->value,
            ]);
        }

        Tank::query()->whereKey($batch->tank_id)->lockForUpdate()->firstOrFail();

        $this->tankRepository->update((string) $batch->tank_id, [
            'status' => 'active',
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function resolveTotalRevenue(Sale $sale, array $attributes): float
    {
        $weight = isset($attributes['total_weight'])
            ? (float) $attributes['total_weight']
            : (float) $sale->total_weight;
        $price = isset($attributes['price_per_kg'])
            ? (float) $attributes['price_per_kg']
            : (float) $sale->price_per_kg;

        return round($weight * $price, 2);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function buildAttributes(Sale $sale, array $data): array
    {
        $attributes = [];

        if (array_key_exists('client_id', $data)) {
            $attributes['client_id'] = $data['client_id'];
        }

        if (array_key_exists('total_weight', $data)) {
            $attributes['total_weight'] = (float) $data['total_weight'];
        }

        if (array_key_exists('price_per_kg', $data)) {
            $attributes['price_per_kg'] = (float) $data['price_per_kg'];
        }

        if (isset($attributes['total_weight']) || isset($attributes['price_per_kg'])) {
            $weight = $attributes['total_weight'] ?? (float) $sale->total_weight;
            $price  = $attributes['price_per_kg'] ?? (float) $sale->price_per_kg;

            $attributes['total_revenue'] = round($weight * $price, 2);
        }

        if (array_key_exists('sale_date', $data)) {
            $attributes['sale_date'] = $data['sale_date'];
        }

        if (array_key_exists('status', $data)) {
            $attributes['status'] = SaleStatus::from((string) $data['status'])->value;
        }

        if (array_key_exists('notes', $data)) {
            $attributes['notes'] = $data['notes'];
        }

        if (array_key_exists('is_total_harvest', $data)) {
            $attributes['is_total_harvest'] = (bool) $data['is_total_harvest'];
        }

        return $attributes;
    }
}
