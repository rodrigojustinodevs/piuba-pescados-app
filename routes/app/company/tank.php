<?php

declare(strict_types=1);

use App\Presentation\Controllers\TankController;
use Illuminate\Support\Facades\Route;

Route::post('tank', [TankController::class, 'store']);
Route::get('tanks', [TankController::class, 'index']);
Route::get('tank/{id}', [TankController::class, 'show']);
Route::put('tank/{id}', [TankController::class, 'update']);
Route::delete('tank/{id}', [TankController::class, 'destroy']);
