<?php

declare(strict_types=1);

use App\Presentation\Controllers\FeedControlController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-feed-control|view-feed-control|update-feed-control|delete-feed-control'])
    ->group(function (): void {
        Route::post('feed-control', [FeedControlController::class, 'store']);
        Route::get('feed-controls', [FeedControlController::class, 'index']);
        Route::get('feed-control/{id}', [FeedControlController::class, 'show']);
        Route::put('feed-control/{id}', [FeedControlController::class, 'update']);
        Route::delete('feed-control/{id}', [FeedControlController::class, 'destroy']);
    });
