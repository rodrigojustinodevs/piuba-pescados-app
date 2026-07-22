<?php

declare(strict_types=1);

use App\Presentation\Controllers\TankHistoryController;
use Illuminate\Support\Facades\Route;

Route::post('tank-history', [TankHistoryController::class, 'store'])
    ->middleware('permission:create-tank-history');

Route::get('tank-histories', [TankHistoryController::class, 'index'])
    ->middleware('permission:view-tank-history');
