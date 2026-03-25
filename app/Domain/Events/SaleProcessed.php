<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\Sale;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by ProcessHarvestSaleUseCase after a sale is successfully persisted.
 * Listened to by GenerateStockingHistory when the sale is linked to a stocking (despesca).
 */
final readonly class SaleProcessed implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Sale $sale,
    ) {
    }
}
