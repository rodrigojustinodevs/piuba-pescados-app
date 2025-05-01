<?php

declare(strict_types=1);

use App\Presentation\Controllers\CostAllocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(
    ['permission:create-cost-allocation|view-cost-allocation|update-cost-allocation|delete-cost-allocation']
)->group(function (): void {
    Route::post('cost-allocation', [CostAllocationController::class, 'store']);
    Route::get('cost-allocations', [CostAllocationController::class, 'index']);
    Route::get('cost-allocation/{id}', [CostAllocationController::class, 'show']);
    Route::put('cost-allocation/{id}', [CostAllocationController::class, 'update']);
    Route::delete('cost-allocation/{id}', [CostAllocationController::class, 'destroy']);
});
