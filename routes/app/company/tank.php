<?php

declare(strict_types=1);

use App\Presentation\Controllers\TankController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-tank'])
    ->group(function (): void {
        Route::post('tank', [TankController::class, 'store']);
    });

Route::middleware(['permission:view-tank'])
    ->group(function (): void {
        Route::get('tanks', [TankController::class, 'index']);
        Route::get('tanks/without-batches', [TankController::class, 'tanksWithoutBatches']);
        Route::get('tank/{id}', [TankController::class, 'show']);
    });

Route::middleware(['permission:update-tank'])
    ->group(function (): void {
        Route::put('tank/{id}', [TankController::class, 'update']);
    });

Route::middleware(['permission:delete-tank'])
    ->group(function (): void {
        Route::delete('tank/{id}', [TankController::class, 'destroy']);
    });
