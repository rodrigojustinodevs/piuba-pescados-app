<?php

declare(strict_types=1);

use App\Presentation\Controllers\HarvestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-harvest|view-harvest|update-harvest|delete-harvest'])
    ->group(function (): void {
        Route::post('harvest', [HarvestController::class, 'store']);
        Route::get('harvests', [HarvestController::class, 'index']);
        Route::get('harvest/{id}', [HarvestController::class, 'show']);
        Route::put('harvest/{id}', [HarvestController::class, 'update']);
        Route::delete('harvest/{id}', [HarvestController::class, 'destroy']);
    });
