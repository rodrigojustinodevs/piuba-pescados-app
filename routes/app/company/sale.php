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

Route::middleware(['permission:cancel-sale'])
    ->patch('sale/{id}/cancel', [SaleController::class, 'cancel']);

Route::middleware(['permission:update-sale'])
    ->patch('sale/{id}/pay', [SaleController::class, 'pay']);

Route::middleware(['permission:update-sale'])
    ->patch('sale/{id}/deliver', [SaleController::class, 'deliver']);

Route::middleware(['permission:view-sale'])
    ->get('sale/{id}/payments', [SaleController::class, 'payments']);

Route::middleware(['permission:update-sale'])
    ->post('sale/{id}/payments', [SaleController::class, 'storePayment']);
