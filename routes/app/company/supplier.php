<?php

declare(strict_types=1);

use App\Presentation\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::post('supplier', [SupplierController::class, 'store']);
Route::get('suppliers', [SupplierController::class, 'index']);
Route::get('supplier/{id}', [SupplierController::class, 'show']);
Route::put('supplier/{id}', [SupplierController::class, 'update']);
Route::delete('supplier/{id}', [SupplierController::class, 'destroy']);
