<?php

declare(strict_types=1);

use App\Presentation\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-client|view-client|update-client|delete-client'])
    ->group(function (): void {
        Route::post('client', [ClientController::class, 'store']);
        Route::get('clients', [ClientController::class, 'index']);
        Route::get('client/{id}', [ClientController::class, 'show']);
        Route::put('client/{id}', [ClientController::class, 'update']);
        Route::delete('client/{id}', [ClientController::class, 'destroy']);
    });
