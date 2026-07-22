<?php

declare(strict_types=1);

use App\Presentation\Controllers\FeedInventoryController;
use Illuminate\Support\Facades\Route;

Route::post('feed-inventory', [FeedInventoryController::class, 'store'])
    ->middleware('permission:create-feed-inventory');

Route::get('feed-inventories', [FeedInventoryController::class, 'index'])
    ->middleware('permission:view-feed-inventory');

Route::get('feed-inventory/{id}', [FeedInventoryController::class, 'show'])
    ->middleware('permission:view-feed-inventory');

Route::put('feed-inventory/{id}', [FeedInventoryController::class, 'update'])
    ->middleware('permission:update-feed-inventory');

Route::delete('feed-inventory/{id}', [FeedInventoryController::class, 'destroy'])
    ->middleware('permission:delete-feed-inventory');
