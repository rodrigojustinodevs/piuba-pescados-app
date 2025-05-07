<?php

declare(strict_types=1);

use App\Presentation\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('transfer', [TransferController::class, 'store']);
Route::get('transfers', [TransferController::class, 'index']);
Route::get('transfer/{id}', [TransferController::class, 'show']);
Route::put('transfer/{id}', [TransferController::class, 'update']);
Route::delete('transfer/{id}', [TransferController::class, 'destroy']);
