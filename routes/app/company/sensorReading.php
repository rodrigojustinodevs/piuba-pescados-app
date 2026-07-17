<?php

declare(strict_types=1);

use App\Presentation\Controllers\SensorReadingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-sensor-reading'])
    ->group(static function (): void {
        Route::post('sensor-reading', [SensorReadingController::class, 'store']);
    });

Route::middleware(['permission:view-sensor-reading'])
    ->group(static function (): void {
        Route::get('sensor-readings', [SensorReadingController::class, 'index']);
        Route::get('sensor-reading/{id}', [SensorReadingController::class, 'show']);
    });

Route::middleware(['permission:update-sensor-reading'])
    ->group(static function (): void {
        Route::put('sensor-reading/{id}', [SensorReadingController::class, 'update']);
    });

Route::middleware(['permission:delete-sensor-reading'])
    ->group(static function (): void {
        Route::delete('sensor-reading/{id}', [SensorReadingController::class, 'destroy']);
    });
