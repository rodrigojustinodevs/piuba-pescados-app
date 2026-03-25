<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\Mortality;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by CreateMortalityUseCase after a mortality record is successfully persisted.
 * Listened to by GenerateStockingHistory to create an automatic history entry.
 */
final readonly class MortalityRecorded implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Mortality $mortality,
        public string $companyId,
    ) {
    }
}
