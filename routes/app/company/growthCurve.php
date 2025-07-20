<?php

declare(strict_types=1);

use App\Presentation\Controllers\GrowthCurveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-growth-curve'])
    ->post('growth-curve', [GrowthCurveController::class, 'store']);

Route::middleware(['permission:view-growth-curve'])
    ->get('growth-curves', [GrowthCurveController::class, 'index']);

Route::middleware(['permission:view-growth-curve'])
    ->get('growth-curve/{id}', [GrowthCurveController::class, 'show']);

Route::middleware(['permission:update-growth-curve'])
    ->put('growth-curve/{id}', [GrowthCurveController::class, 'update']);

Route::middleware(['permission:delete-growth-curve'])
    ->delete('growth-curve/{id}', [GrowthCurveController::class, 'destroy']);
