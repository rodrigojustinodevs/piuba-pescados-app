<?php

declare(strict_types=1);

use App\Presentation\Controllers\SensorController;
use Illuminate\Support\Facades\Route;

Route::post('sensor', [SensorController::class, 'store'])
    ->middleware('permission:create-sensor');

Route::get('sensors', [SensorController::class, 'index'])
    ->middleware('permission:view-sensor');

Route::get('sensor/{id}', [SensorController::class, 'show'])
    ->middleware('permission:view-sensor');

Route::put('sensor/{id}', [SensorController::class, 'update'])
    ->middleware('permission:update-sensor');

Route::delete('sensor/{id}', [SensorController::class, 'destroy'])
    ->middleware('permission:delete-sensor');
