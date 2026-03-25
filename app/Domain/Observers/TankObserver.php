<?php

declare(strict_types=1);

namespace App\Domain\Observers;

use App\Domain\Enums\TankHistoryEvent;
use App\Domain\Models\Tank;
use App\Domain\Models\TankHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Watches Tank model for status changes that happen OUTSIDE of explicit
 * history creation (CreateTankHistoryUseCase).
 *
 * CreateTankHistoryUseCase uses updateQuietly() to suppress this observer
 * when it already persists an explicit history entry (cleaning/maintenance/fallowing).
 *
 * This observer fires for all other status transitions, e.g.:
 *  - Tank reactivated to 'active' after maintenance
 *  - Status changed by batch allocation or admin action
 */
final class TankObserver
{
    public function updated(Tank $tank): void
    {
        if (! $tank->isDirty('status')) {
            return;
        }

        $oldStatus = (string) $tank->getOriginal('status');
        $newStatus = (string) $tank->status;

        TankHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $tank->company_id,
            'tank_id'     => $tank->id,
            'event'       => TankHistoryEvent::STATUS_CHANGE->value,
            'event_date'  => now()->toDateString(),
            'description' => sprintf(
                'Status alterado de "%s" para "%s".',
                $oldStatus,
                $newStatus,
            ),
            'performed_by' => Auth::id(),
        ]);
    }
}
