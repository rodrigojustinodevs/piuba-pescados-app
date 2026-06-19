<?php

declare(strict_types=1);

use App\Presentation\Controllers\FeedingController;
use Illuminate\Support\Facades\Route;

Route::post('feeding', [FeedingController::class, 'store'])
    ->middleware('permission:create-feeding');
Route::get('feedings', [FeedingController::class, 'index'])
    ->middleware('permission:view-feeding');
Route::get('feeding/{id}', [FeedingController::class, 'show'])
    ->middleware('permission:view-feeding');
Route::put('feeding/{id}', [FeedingController::class, 'update'])
    ->middleware('permission:update-feeding');
Route::delete('feeding/{id}', [FeedingController::class, 'destroy'])
    ->middleware('permission:delete-feeding');
