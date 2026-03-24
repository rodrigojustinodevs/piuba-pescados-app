<?php

declare(strict_types=1);

use App\Presentation\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
    ->middleware(['permission:view-dashboard'])
    ->group(function (): void {
        Route::get('summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
        Route::get('alerts', [DashboardController::class, 'alerts'])->name('dashboard.alerts');
        Route::get('trends', [DashboardController::class, 'trends'])->name('dashboard.trends');
    });
