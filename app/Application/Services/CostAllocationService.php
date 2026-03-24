<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Enums\AllocationMethod;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\AllocationAmountMismatchException;
use App\Domain\Exceptions\InactiveStockingException;
use App\Domain\Exceptions\TransactionAlreadyAllocatedException;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use InvalidArgumentException;

final readonly class CostAllocationService
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Guard: Transaction
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validates the financial transaction before creating a cost allocation.
     *
     * Rules enforced:
     * - Must exist and belong to the company
     * - Must be of type EXPENSE
     * - Status must be PENDING or PAID (not cancelled/overdue)
     * - Must not have been allocated already (is_allocated = false)
     *
     * @throws InvalidArgumentException
     * @throws TransactionAlreadyAllocatedException
     */
    public function guardTransaction(string $transactionId): FinancialTransaction
    {
        $transaction = $this->transactionRepository->findOrFail($transactionId);

        if ($transaction->type !== FinancialType::EXPENSE) {
            throw new InvalidArgumentException(
                'Only transactions of the type Expense can be allocated.'
            );
        }

        $allowedStatuses = [FinancialTransactionStatus::PENDING, FinancialTransactionStatus::PAID];

        if (! in_array($transaction->status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                "Only expenses with status 'pending' or 'paid' can be allocated. "
                . "Current status: {$transaction->status->value}."
            );
        }

        if ($transaction->isAllocated()) {
            throw new TransactionAlreadyAllocatedException($transactionId);
        }

        return $transaction;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Guard: Stockings
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validates that all stockings are linked to ACTIVE batches.
     *
     * @param  string[] $stockingIds
     * @return Stocking[]
     *
     * @throws InactiveStockingException
     */
    public function guardStockings(array $stockingIds): array
    {
        $stockings = Stocking::with('batch')->findMany($stockingIds)->all();

        foreach ($stockings as $stocking) {
            $batch       = $stocking->batch;
            $batchStatus = $batch !== null ? $batch->status : 'unknown';

            if ($batchStatus !== 'active') {
                throw new InactiveStockingException($stocking->id, $batchStatus);
            }
        }

        return $stockings;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Core: Amount computation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Distributes $totalAmount among the given stockings using the chosen method.
     * Guarantees the sum of returned amounts equals exactly $totalAmount
     * via penny-rounding on the last item.
     *
     * @param  Stocking[] $stockings  Validated, eager-loaded stocking models.
     * @return array<int, array{stocking_id: string, percentage: float, amount: float}>
     *
     * @throws InvalidArgumentException when a method requires data that is missing (e.g. zero volume)
     */
    public function computeAmounts(
        AllocationMethod $method,
        float $totalAmount,
        array $stockings,
    ): array {
        $factors = match ($method) {
            AllocationMethod::FLAT    => $this->flatFactors($stockings),
            AllocationMethod::BIOMASS => $this->biomassFactors($stockings),
            AllocationMethod::VOLUME  => $this->volumeFactors($stockings),
        };

        return $this->applyPennyRounding($stockings, $factors, $totalAmount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private: Factor calculators
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  Stocking[] $stockings
     * @return float[]  Indexed by stocking position (same order as input).
     */
    private function flatFactors(array $stockings): array
    {
        $count = count($stockings);

        if ($count === 0) {
            throw new InvalidArgumentException('No stockings provided for allocation.');
        }

        return array_fill(0, $count, 1.0 / $count);
    }

    /**
     * @param  Stocking[] $stockings
     * @return float[]
     */
    private function biomassFactors(array $stockings): array
    {
        $biomasses = array_map(
            static fn (Stocking $s): float => $s->initialBiomass(),
            $stockings,
        );
        $totalBiomass = array_sum($biomasses);

        if ($totalBiomass <= 0) {
            throw new InvalidArgumentException(
                'The total biomass of the selected batches is zero. '
                . 'Check the quantity and average weight fields of the stockings.'
            );
        }

        return array_map(
            static fn (float $b): float => $b / $totalBiomass,
            $biomasses,
        );
    }

    /**
     * Uses the tank's capacity_liters as the volume proxy.
     *
     * @param  Stocking[] $stockings  Must have batch → tank eager-loaded.
     * @return float[]
     */
    private function volumeFactors(array $stockings): array
    {
        // Ensure batch.tank is loaded
        foreach ($stockings as $stocking) {
            if (! $stocking->relationLoaded('batch')) {
                $stocking->load('batch.tank');
            } elseif ($stocking->batch !== null && ! $stocking->batch->relationLoaded('tank')) {
                $stocking->batch->load('tank');
            }
        }

        $volumes = array_map(
            static fn (Stocking $s): float => (float) ($s->batch?->tank->capacity_liters ?? 0),
            $stockings,
        );
        $totalVolume = array_sum($volumes);

        if ($totalVolume <= 0) {
            throw new InvalidArgumentException(
                'The total volume (capacity_liters) of the selected tanks is zero. '
                . 'Check the data of the tanks linked to the batches.'
            );
        }

        return array_map(
            static fn (float $v): float => $v / $totalVolume,
            $volumes,
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private: Penny rounding
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Converts factor ratios into concrete amounts, adjusting the LAST item
     * by the rounding delta to ensure sum = totalAmount exactly.
     *
     * @param  Stocking[] $stockings
     * @param  float[]    $factors    One per stocking, must sum to ≈ 1.0.
     * @return array<int, array{stocking_id: string, percentage: float, amount: float}>
     */
    private function applyPennyRounding(
        array $stockings,
        array $factors,
        float $totalAmount,
    ): array {
        $items      = [];
        $runningSum = 0.0;
        $lastIndex  = count($stockings) - 1;

        foreach ($stockings as $index => $stocking) {
            $factor     = $factors[$index];
            $percentage = round($factor * 100, 4);

            if ($index === $lastIndex) {
                // Absorb any rounding delta in the last item
                $amount = round($totalAmount - $runningSum, 2);
            } else {
                $amount = round($totalAmount * $factor, 2);
                $runningSum += $amount;
            }

            $items[] = [
                'stocking_id' => $stocking->id,
                'percentage'  => $percentage,
                'amount'      => $amount,
            ];
        }

        // Sanity check after rounding (should never fail, but guarantees audit safety)
        $computedSum = array_sum(array_column($items, 'amount'));

        if (abs($computedSum - $totalAmount) > 0.01) {
            throw new AllocationAmountMismatchException(
                expected: $totalAmount,
                actual:   $computedSum,
            );
        }

        return $items;
    }
}
