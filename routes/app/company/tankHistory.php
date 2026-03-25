<?php

declare(strict_types=1);

use App\Presentation\Controllers\TankHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-tank-history|view-tank-history'])
    ->group(function (): void {
        Route::post('tank-history', [TankHistoryController::class, 'store']);
        Route::get('tank-histories', [TankHistoryController::class, 'index']);
    });
