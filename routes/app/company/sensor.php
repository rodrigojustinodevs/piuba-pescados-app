<?php

declare(strict_types=1);

use App\Presentation\Controllers\SensorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-sensor|view-sensor|update-sensor|delete-sensor'])
    ->group(function (): void {
        Route::post('sensor', [SensorController::class, 'store']);
        Route::get('sensors', [SensorController::class, 'index']);
        Route::get('sensor/{id}', [SensorController::class, 'show']);
        Route::put('sensor/{id}', [SensorController::class, 'update']);
        Route::delete('sensor/{id}', [SensorController::class, 'destroy']);
    });
