<?php

declare(strict_types=1);

use App\Presentation\Controllers\HarvestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-harvest'])
    ->post('harvest', [HarvestController::class, 'store']);

Route::middleware(['permission:view-harvest'])
    ->get('harvests', [HarvestController::class, 'index']);

Route::middleware(['permission:view-harvest'])
    ->get('harvest/{id}', [HarvestController::class, 'show']);

Route::middleware(['permission:update-harvest'])
    ->put('harvest/{id}', [HarvestController::class, 'update']);

Route::middleware(['permission:delete-harvest'])
    ->delete('harvest/{id}', [HarvestController::class, 'destroy']);
