<?php

declare(strict_types=1);

use App\Presentation\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::post('settlement', [SettlementController::class, 'store']);
Route::get('settlements', [SettlementController::class, 'index']);
Route::get('settlement/{id}', [SettlementController::class, 'show']);
Route::put('settlement/{id}', [SettlementController::class, 'update']);
Route::delete('settlement/{id}', [SettlementController::class, 'destroy']);
