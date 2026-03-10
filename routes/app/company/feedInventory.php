<?php

declare(strict_types=1);

use App\Presentation\Controllers\FeedInventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-feed-inventory|view-feed-inventory|update-feed-inventory|delete-feed-inventory'])
    ->group(function (): void {
        Route::post('feed-inventory', [FeedInventoryController::class, 'store']);
        Route::get('feed-inventories', [FeedInventoryController::class, 'index']);
        Route::get('feed-inventory/{id}', [FeedInventoryController::class, 'show']);
        Route::put('feed-inventory/{id}', [FeedInventoryController::class, 'update']);
        Route::delete('feed-inventory/{id}', [FeedInventoryController::class, 'destroy']);
    });
