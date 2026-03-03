<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockingController;
use Illuminate\Support\Facades\Route;

Route::post('stocking', [StockingController::class, 'store']);
Route::get('stockings', [StockingController::class, 'index']);
Route::get('stocking/{id}', [StockingController::class, 'show']);
Route::put('stocking/{id}', [StockingController::class, 'update']);
Route::delete('stocking/{id}', [StockingController::class, 'destroy']);
