<?php

declare(strict_types=1);

use App\Presentation\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::post('purchase', [PurchaseController::class, 'store'])
    ->middleware('permission:create-purchase');

Route::get('purchases', [PurchaseController::class, 'index'])
    ->middleware('permission:view-purchase');

Route::get('purchase/{id}', [PurchaseController::class, 'show'])
    ->middleware('permission:view-purchase');

Route::put('purchase/{id}', [PurchaseController::class, 'update'])
    ->middleware('permission:update-purchase');

Route::delete('purchase/{id}', [PurchaseController::class, 'destroy'])
    ->middleware('permission:delete-purchase');

Route::patch('purchase/{id}/receive', [PurchaseController::class, 'receive'])
    ->middleware('permission:update-purchase');

Route::patch('purchase/{id}/cancel', [PurchaseController::class, 'cancel'])
    ->middleware('permission:update-purchase');

Route::get('purchase/{id}/payments', [PurchaseController::class, 'getPayments'])
    ->middleware('permission:view-purchase');

Route::post('purchase/{id}/payments', [PurchaseController::class, 'registerPayment'])
    ->middleware('permission:update-purchase');
