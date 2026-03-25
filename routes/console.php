<?php

declare(strict_types=1);

use App\Console\Commands\MarkOverdueClientsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Daily agenda: mark/unmark overdue clients.
 * To activate, uncomment the block below or add it to the Console Kernel.
 */
// app(Schedule::class)->command(MarkOverdueClientsCommand::class)->dailyAt('02:00');
