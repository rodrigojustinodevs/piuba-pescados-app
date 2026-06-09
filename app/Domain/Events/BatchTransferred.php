<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\Transfer;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by CreateTransferUseCase after a transfer is successfully persisted.
 * Listened to by RecordBatchTransferHistory to create a StockingHistory entry.
 */
final readonly class BatchTransferred implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Transfer $transfer,
        public string $companyId,
    ) {
    }
}
