<?php

declare(strict_types=1);

use App\Presentation\Controllers\FinancialTransactionController;
use Illuminate\Support\Facades\Route;

Route::post('financial-transaction', [FinancialTransactionController::class, 'store'])
    ->middleware('permission:create-financial-transaction');

Route::get('financial-transactions', [FinancialTransactionController::class, 'index'])
    ->middleware('permission:view-financial-transaction');

Route::get('financial-transaction/{id}', [FinancialTransactionController::class, 'show'])
    ->middleware('permission:view-financial-transaction');

Route::put('financial-transaction/{id}', [FinancialTransactionController::class, 'update'])
    ->middleware('permission:update-financial-transaction');

Route::delete('financial-transaction/{id}', [FinancialTransactionController::class, 'destroy'])
    ->middleware('permission:delete-financial-transaction');
