<?php

declare(strict_types=1);

use App\Presentation\Controllers\SalesOrderController;
use App\Presentation\Controllers\SalesQuotationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:view-sale-order'])
    ->get('sale-orders', [SalesOrderController::class, 'index']);

Route::middleware(['permission:view-sale-order'])
    ->get('sale-orders/quotations', [SalesQuotationController::class, 'index']);

Route::middleware(['permission:view-sale-order'])
    ->get('sale-order/{id}', [SalesOrderController::class, 'show']);

Route::middleware(['permission:view-sale-order'])
    ->get('sale-order/quotation/{id}', [SalesQuotationController::class, 'show']);

Route::middleware(['permission:create-sale-order'])
    ->post('sale-order/quotation', [SalesQuotationController::class, 'store']);

Route::middleware(['permission:create-sale-order'])
    ->post('sale-order', [SalesOrderController::class, 'store']);

Route::middleware(['permission:update-sale-order'])
    ->put('sale-order/{id}', [SalesOrderController::class, 'update']);

Route::middleware(['permission:update-sale-order'])
    ->post('sale-order/quotation/{id}/convert', [SalesQuotationController::class, 'convert']);

Route::middleware(['permission:update-sale-order'])
    ->put('sale-order/quotation/{id}', [SalesQuotationController::class, 'update']);

Route::middleware(['permission:delete-sale-order'])
    ->delete('sale-order/{id}', [SalesOrderController::class, 'destroy']);

Route::middleware(['permission:cancel-sale-order'])
    ->delete('sale-order/{id}/cancel', [SalesOrderController::class, 'cancel']);
