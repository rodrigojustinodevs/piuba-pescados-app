<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-stock|view-stock|update-stock|delete-stock'])
    ->group(function (): void {
        Route::post('stock', [StockController::class, 'store']);
        Route::get('stocks', [StockController::class, 'index']);
        Route::get('stock/{id}', [StockController::class, 'show']);
        Route::put('stock/{id}', [StockController::class, 'update']);
        Route::delete('stock/{id}', [StockController::class, 'destroy']);
        Route::patch('stock/{id}/adjust', [StockController::class, 'adjust']);
    });
