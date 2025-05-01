<?php

declare(strict_types=1);

use App\Presentation\Controllers\WaterQualityController;
use Illuminate\Support\Facades\Route;

Route::post('water-quality', [WaterQualityController::class, 'store']);
Route::get('water-qualities', [WaterQualityController::class, 'index']);
Route::get('water-quality/{id}', [WaterQualityController::class, 'show']);
Route::put('water-quality/{id}', [WaterQualityController::class, 'update']);
Route::delete('water-quality/{id}', [WaterQualityController::class, 'destroy']);
