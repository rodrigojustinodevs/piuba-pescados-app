<?php

declare(strict_types=1);

use App\Presentation\Controllers\MortalityController;
use Illuminate\Support\Facades\Route;

Route::post('mortality', [MortalityController::class, 'store'])
    ->middleware('permission:create-mortality');

Route::get('mortalities', [MortalityController::class, 'index'])
    ->middleware('permission:view-mortality');

Route::get('mortality/{id}', [MortalityController::class, 'show'])
    ->middleware('permission:view-mortality');

Route::put('mortality/{id}', [MortalityController::class, 'update'])
    ->middleware('permission:update-mortality');

Route::delete('mortality/{id}', [MortalityController::class, 'destroy'])
    ->middleware('permission:delete-mortality');

Route::get('/batch/{batchId}/survival-rate', [MortalityController::class, 'survivalRate'])
    ->middleware('permission:view-mortality');
