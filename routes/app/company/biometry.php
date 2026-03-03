<?php

declare(strict_types=1);

use App\Presentation\Controllers\BiometryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-batch|view-batch|update-batch|delete-batch'])
    ->group(function (): void {
        Route::post('biometry', [BiometryController::class, 'store']);
        Route::get('biometries', [BiometryController::class, 'index']);
        Route::get('biometry/{id}', [BiometryController::class, 'show']);
        Route::put('biometry/{id}', [BiometryController::class, 'update']);
        Route::delete('biometry/{id}', [BiometryController::class, 'destroy']);
    });
