<?php

declare(strict_types=1);

use App\Presentation\Controllers\MortalityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-mortality|view-mortality|update-mortality|delete-mortality'])
    ->group(function (): void {
        Route::post('mortality', [MortalityController::class, 'store']);
        Route::get('mortalities', [MortalityController::class, 'index']);
        Route::get('mortality/{id}', [MortalityController::class, 'show']);
        Route::put('mortality/{id}', [MortalityController::class, 'update']);
        Route::delete('mortality/{id}', [MortalityController::class, 'destroy']);
    });
