<?php

declare(strict_types=1);

use App\Presentation\Controllers\UserController;
use App\Presentation\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function()  {
    return 'pong';
 });
Route::post('/company', [CompanyController::class, 'store']);
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/company/{id}', [CompanyController::class, 'show']);
Route::put('/company/{id}', [CompanyController::class, 'update']);
Route::delete('/company/{id}', [CompanyController::class, 'destroy']);
Route::get('/user', [UserController::class, 'index']);

