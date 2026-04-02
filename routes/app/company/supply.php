<?php

declare(strict_types=1);

use App\Presentation\Controllers\SupplyController;
use Illuminate\Support\Facades\Route;

Route::post('/supply', [SupplyController::class, 'store']);
Route::get('/supplies', [SupplyController::class, 'index']);
Route::get('/supply/{id}', [SupplyController::class, 'show']);
Route::put('/supply/{id}', [SupplyController::class, 'update']);
