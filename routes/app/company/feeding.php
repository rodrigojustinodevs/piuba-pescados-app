<?php

declare(strict_types=1);

use App\Presentation\Controllers\FeedingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-feeding|view-feeding|update-feeding|delete-feeding'])
    ->group(function (): void {
        Route::post('feeding', [FeedingController::class, 'store']);
        Route::get('feedings', [FeedingController::class, 'index']);
        Route::get('feeding/{id}', [FeedingController::class, 'show']);
        Route::put('feeding/{id}', [FeedingController::class, 'update']);
        Route::delete('feeding/{id}', [FeedingController::class, 'destroy']);
    });
