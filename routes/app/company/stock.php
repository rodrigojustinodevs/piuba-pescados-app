<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::post('stock', [StockController::class, 'store']);
Route::get('stocks', [StockController::class, 'index']);
Route::get('stock/{id}', [StockController::class, 'show']);
Route::put('stock/{id}', [StockController::class, 'update']);
Route::delete('stock/{id}', [StockController::class, 'destroy']);
