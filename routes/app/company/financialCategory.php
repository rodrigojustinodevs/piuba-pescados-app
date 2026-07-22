<?php

declare(strict_types=1);

use App\Presentation\Controllers\FinancialCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('financial-categories', [FinancialCategoryController::class, 'index'])
    ->middleware('permission:view-financial-category');

Route::get('financial-category/{id}', [FinancialCategoryController::class, 'show'])
    ->middleware('permission:view-financial-category');

Route::post('financial-category', [FinancialCategoryController::class, 'store'])
    ->middleware('permission:create-financial-category');

Route::put('financial-category/{id}', [FinancialCategoryController::class, 'update'])
    ->middleware('permission:update-financial-category');

Route::delete('financial-category/{id}', [FinancialCategoryController::class, 'destroy'])
    ->middleware('permission:delete-financial-category');

Route::patch('financial-category/{id}/inactive', [FinancialCategoryController::class, 'inactive'])
    ->middleware('permission:update-financial-category');

Route::patch('financial-category/{id}/active', [FinancialCategoryController::class, 'active'])
    ->middleware('permission:update-financial-category');
