<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Enums\SaleStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Marks sales as overdue when due_date has passed without payment.
 * Clears overdue status when sales are subsequently paid.
 *
 * Suggested schedule: daily at 01:00 via routes/console.php
 *   app(Schedule::class)->command(MarkOverdueSalesCommand::class)->dailyAt('01:00');
 */
class MarkOverdueSalesCommand extends Command
{
    protected $signature = 'sales:mark-overdue
                            {--dry-run : Display affected sales without persisting changes}';

    protected $description = 'Marks/unmarks sales as overdue based on due_date and payment status';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->markOverdue($dryRun);
        $this->clearOverdue($dryRun);

        $this->info('Sales overdue synchronization complete.');

        return self::SUCCESS;
    }

    private function markOverdue(bool $dryRun): void
    {
        $ids = DB::table('sales')
            ->whereNull('deleted_at')
            ->whereNotNull('due_date')
            ->whereNull('paid_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereIn('status', [SaleStatus::PENDING->value, SaleStatus::CONFIRMED->value])
            ->pluck('id')
            ->all();

        if (empty($ids)) {
            $this->line('No sales to mark as overdue.');

            return;
        }

        $this->line(sprintf('Marking %d sale(s) as overdue...', count($ids)));

        if (! $dryRun) {
            DB::table('sales')
                ->whereIn('id', $ids)
                ->update(['status' => SaleStatus::OVERDUE->value, 'updated_at' => now()]);

            Log::info('sales:mark-overdue — sales marked as overdue', ['ids' => $ids]);
        }
    }

    private function clearOverdue(bool $dryRun): void
    {
        $ids = DB::table('sales')
            ->whereNull('deleted_at')
            ->where('status', SaleStatus::OVERDUE->value)
            ->whereNotNull('paid_date')
            ->pluck('id')
            ->all();

        if (empty($ids)) {
            $this->line('No overdue sales to clear.');

            return;
        }

        $this->line(sprintf('Clearing overdue status for %d sale(s)...', count($ids)));

        if (! $dryRun) {
            DB::table('sales')
                ->whereIn('id', $ids)
                ->update(['status' => SaleStatus::PAID->value, 'updated_at' => now()]);

            Log::info('sales:mark-overdue — overdue status cleared for paid sales', ['ids' => $ids]);
        }
    }
}
