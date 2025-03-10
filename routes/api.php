<?php

declare(strict_types=1);

use App\Presentation\Controllers\CompanyController;
use App\Presentation\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn (): string => 'pong');
Route::post('/company', [CompanyController::class, 'store']);
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/company/{id}', [CompanyController::class, 'show']);
Route::put('/company/{id}', [CompanyController::class, 'update']);
Route::delete('/company/{id}', [CompanyController::class, 'destroy']);
