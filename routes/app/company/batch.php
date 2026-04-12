<?php

declare(strict_types=1);

use App\Presentation\Controllers\BatchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-batch|view-batch|update-batch|delete-batch'])
    ->group(function (): void {
        Route::post('batch', [BatchController::class, 'store']);
        Route::get('batches', [BatchController::class, 'index']);
        Route::get('batch/{id}', [BatchController::class, 'show']);
        Route::put('batch/{id}', [BatchController::class, 'update']);
        Route::delete('batch/{id}', [BatchController::class, 'destroy']);
        Route::post('batch/{id}/finish', [BatchController::class, 'finish']);
        Route::post('batches/distribution', [BatchController::class, 'distribution']);
    });
