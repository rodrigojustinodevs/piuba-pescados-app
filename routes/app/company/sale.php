<?php

declare(strict_types=1);

use App\Presentation\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-sale'])
    ->post('sale', [SaleController::class, 'store']);

Route::middleware(['permission:view-sale'])
    ->get('sales', [SaleController::class, 'index']);

Route::middleware(['permission:view-sale'])
    ->get('sale/{id}', [SaleController::class, 'show']);

Route::middleware(['permission:update-sale'])
    ->put('sale/{id}', [SaleController::class, 'update']);

Route::middleware(['permission:delete-sale'])
    ->delete('sale/{id}', [SaleController::class, 'destroy']);
