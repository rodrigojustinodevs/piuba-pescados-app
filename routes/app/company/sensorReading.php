<?php

declare(strict_types=1);

use App\Presentation\Controllers\SensorReadingController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'permission:create-sensor-reading|view-sensor-reading|update-sensor-reading|delete-sensor-reading',
])
    ->group(static function (): void {
        Route::post('sensor-reading', [SensorReadingController::class, 'store']);
        Route::get('sensor-readings', [SensorReadingController::class, 'index']);
        Route::get('sensor-reading/{id}', [SensorReadingController::class, 'show']);
        Route::put('sensor-reading/{id}', [SensorReadingController::class, 'update']);
        Route::delete('sensor-reading/{id}', [SensorReadingController::class, 'destroy']);
    });
