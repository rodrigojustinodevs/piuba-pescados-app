<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\Feeding;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by CreateFeedingUseCase after a feeding record is successfully persisted.
 * Listened to by GenerateStockingHistory to create an automatic history entry.
 */
final readonly class FeedingCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Feeding $feeding,
        public string $companyId,
    ) {
    }
}
