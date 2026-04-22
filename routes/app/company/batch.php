<?php

declare(strict_types=1);

use App\Presentation\Controllers\BatchController;
use Illuminate\Support\Facades\Route;

Route::post('batch', [BatchController::class, 'store'])
    ->middleware('permission:create-batch');

Route::get('batches', [BatchController::class, 'index'])
    ->middleware('permission:view-batch');

Route::get('batch/{id}', [BatchController::class, 'show'])
    ->middleware('permission:view-batch');

Route::put('batch/{id}', [BatchController::class, 'update'])
    ->middleware('permission:update-batch');

Route::delete('batch/{id}', [BatchController::class, 'destroy'])
    ->middleware('permission:delete-batch');

Route::post('batch/{id}/finish', [BatchController::class, 'finish'])
    ->middleware('permission:update-batch');

Route::post('batches/distribution', [BatchController::class, 'distribution'])
    ->middleware('permission:create-batch');
