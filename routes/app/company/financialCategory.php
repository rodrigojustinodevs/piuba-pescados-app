<?php

declare(strict_types=1);

use App\Presentation\Controllers\FinancialCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(
    ['permission:create-financial-category|view-financial-category|update-financial-category|delete-financial-category']
)->group(function (): void {
    Route::get('financial-categories', [FinancialCategoryController::class, 'index']);
    Route::get('financial-category/{id}', [FinancialCategoryController::class, 'show']);
    Route::post('financial-category', [FinancialCategoryController::class, 'store']);
    Route::put('financial-category/{id}', [FinancialCategoryController::class, 'update']);
    Route::delete('financial-category/{id}', [FinancialCategoryController::class, 'destroy']);

    Route::patch('financial-category/{id}/inactive', [FinancialCategoryController::class, 'inactive']);
    Route::patch('financial-category/{id}/active', [FinancialCategoryController::class, 'active']);
});
