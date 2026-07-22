<?php

declare(strict_types=1);

use App\Presentation\Controllers\StockingHistoryController;
use Illuminate\Support\Facades\Route;

Route::post('stocking-history', [StockingHistoryController::class, 'store'])
    ->middleware('permission:create-stocking-history');

Route::get('stocking-histories', [StockingHistoryController::class, 'index'])
    ->middleware('permission:view-stocking-history');
