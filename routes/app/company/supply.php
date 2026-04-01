<?php

declare(strict_types=1);

use App\Presentation\Controllers\SupplyController;
use Illuminate\Support\Facades\Route;

Route::get('/supplies', [SupplyController::class, 'index']);
Route::get('/supply/{id}', [SupplyController::class, 'show']);
