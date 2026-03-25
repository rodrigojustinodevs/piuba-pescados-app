<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockingHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-stocking-history|view-stocking-history'])
    ->group(function (): void {
        Route::post('stocking-history', [StockingHistoryController::class, 'store']);
        Route::get('stocking-histories', [StockingHistoryController::class, 'index']);
    });
