<?php

declare(strict_types=1);

use App\Presentation\Controllers\FinancialTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(
    ['permission:create-financial-transaction|view-financial-transaction|update-financial-transaction|delete-financial-transaction']
)->group(function (): void {
    Route::post('financial-transaction', [FinancialTransactionController::class, 'store']);
    Route::get('financial-transactions', [FinancialTransactionController::class, 'index']);
    Route::get('financial-transaction/{id}', [FinancialTransactionController::class, 'show']);
    Route::put('financial-transaction/{id}', [FinancialTransactionController::class, 'update']);
    Route::delete('financial-transaction/{id}', [FinancialTransactionController::class, 'destroy']);
});
