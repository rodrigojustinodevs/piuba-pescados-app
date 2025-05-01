<?php

declare(strict_types=1);

use App\Presentation\Controllers\BatcheController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-batche|view-batche|update-batche|delete-batche'])
    ->group(function (): void {
        Route::post('batche', [BatcheController::class, 'store']);
        Route::get('batches', [BatcheController::class, 'index']);
        Route::get('batche/{id}', [BatcheController::class, 'show']);
        Route::put('batche/{id}', [BatcheController::class, 'update']);
        Route::delete('batche/{id}', [BatcheController::class, 'destroy']);
    });
