<?php

declare(strict_types=1);

use App\Presentation\Controllers\CostAllocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-cost-allocation'])
    ->post('cost-allocation', [CostAllocationController::class, 'store']);

Route::middleware(['permission:view-cost-allocation'])
    ->get('cost-allocations', [CostAllocationController::class, 'index']);

Route::middleware(['permission:view-cost-allocation'])
    ->get('cost-allocation/{id}', [CostAllocationController::class, 'show']);

// PUT is intentionally absent: to correct an allocation, the user must
// reverse (DELETE) and recreate it, preserving immutability of history.
Route::middleware(['permission:delete-cost-allocation'])
    ->delete('cost-allocation/{id}', [CostAllocationController::class, 'destroy']);
