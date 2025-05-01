<?php

declare(strict_types=1);

use App\Presentation\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-purchase|view-purchase|update-purchase|delete-purchase'])
    ->group(function (): void {
        Route::post('purchase', [PurchaseController::class, 'store']);
        Route::get('purchases', [PurchaseController::class, 'index']);
        Route::get('purchase/{id}', [PurchaseController::class, 'show']);
        Route::put('purchase/{id}', [PurchaseController::class, 'update']);
        Route::delete('purchase/{id}', [PurchaseController::class, 'destroy']);
    });
