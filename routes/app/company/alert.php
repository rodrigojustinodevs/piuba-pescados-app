<?php

declare(strict_types=1);

use App\Presentation\Controllers\AlertController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-alert'])
    ->post('alert', [AlertController::class, 'store']);

Route::middleware(['permission:view-alert'])
    ->get('alerts', [AlertController::class, 'index']);

Route::middleware(['permission:view-alert'])
    ->get('alert/{id}', [AlertController::class, 'show']);

Route::middleware(['permission:update-alert'])
    ->put('alert/{id}', [AlertController::class, 'update']);

Route::middleware(['permission:delete-alert'])
    ->delete('alert/{id}', [AlertController::class, 'destroy']);
