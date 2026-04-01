<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:view-stock'])
    ->group(function (): void {
        Route::get('stock-transactions', [StockTransactionController::class, 'index']);
        Route::get('stock/{id}/transactions', [StockTransactionController::class, 'byStock']);
    });
