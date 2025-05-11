<?php

declare(strict_types=1);

use App\Presentation\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-client'])
    ->post('client', [ClientController::class, 'store']);

Route::middleware(['permission:view-client'])
    ->get('clients', [ClientController::class, 'index']);

Route::middleware(['permission:view-client'])
    ->get('client/{id}', [ClientController::class, 'show']);

Route::middleware(['permission:update-client'])
    ->put('client/{id}', [ClientController::class, 'update']);

Route::middleware(['permission:delete-client'])
    ->delete('client/{id}', [ClientController::class, 'destroy']);
