<?php

declare(strict_types=1);

use App\Presentation\Controllers\CompanyController;
use App\Presentation\Controllers\TankController;
use App\Presentation\Controllers\BatcheController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn (): string => 'pong');
Route::post('/company', [CompanyController::class, 'store']);
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/company/{id}', [CompanyController::class, 'show']);
Route::put('/company/{id}', [CompanyController::class, 'update']);
Route::delete('/company/{id}', [CompanyController::class, 'destroy']);

Route::post('tank', [TankController::class, 'store']);
Route::get('tanks', [TankController::class, 'index']);
Route::get('tank/{id}', [TankController::class, 'show']);
Route::put('tank/{id}', [TankController::class, 'update']);
Route::delete('tank/{id}', [TankController::class, 'destroy']);


Route::post('batche', [BatcheController::class, 'store']);
Route::get('batches', [BatcheController::class, 'index']);
Route::get('batche/{id}', [BatcheController::class, 'show']);
Route::put('batche/{id}', [BatcheController::class, 'update']);
Route::delete('batche/{id}', [BatcheController::class, 'destroy']);
