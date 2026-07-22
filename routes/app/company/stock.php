<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::post('stock', [StockController::class, 'store'])
    ->middleware('permission:create-stock');

Route::get('stocks', [StockController::class, 'index'])
    ->middleware('permission:view-stock');

Route::get('stock/{id}', [StockController::class, 'show'])
    ->middleware('permission:view-stock');

Route::put('stock/{id}', [StockController::class, 'update'])
    ->middleware('permission:update-stock');

Route::delete('stock/{id}', [StockController::class, 'destroy'])
    ->middleware('permission:delete-stock');

Route::patch('stock/{id}/adjust', [StockController::class, 'adjust'])
    ->middleware('permission:update-stock');

// Balances & Movements
Route::get('stocks/{id}/balances', [StockController::class, 'balances'])
    ->middleware('permission:view-stock');

Route::get('stocks/{id}/movements', [StockController::class, 'movements'])
    ->middleware('permission:view-stock');

Route::post('stocks/movements', [StockController::class, 'registerMovement'])
    ->middleware('permission:update-stock');

Route::post('stocks/transfers', [StockController::class, 'transfer'])
    ->middleware('permission:update-stock');
