<?php

declare(strict_types=1);

use App\Presentation\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/subscription', [SubscriptionController::class, 'store']);
Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/subscription/{id}', [SubscriptionController::class, 'show']);
Route::put('/subscription/{id}', [SubscriptionController::class, 'update']);
Route::delete('/subscription/{id}', [SubscriptionController::class, 'destroy']);
