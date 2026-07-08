<?php

declare(strict_types=1);

use App\Presentation\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-user'])->post('user', [UserController::class, 'store']);
Route::middleware(['permission:view-user'])->get('users', [UserController::class, 'index']);
Route::middleware(['permission:view-user'])->get('user/{id}', [UserController::class, 'show']);
Route::middleware(['permission:edit-user'])->put('user/{id}', [UserController::class, 'update']);
Route::middleware(['permission:delete-user'])->delete('user/{id}', [UserController::class, 'destroy']);
Route::middleware(['permission:assign-user-role'])->patch('user/{id}/role', [UserController::class, 'updateRole']);
Route::middleware(['permission:edit-user'])->patch('user/{id}/status', [UserController::class, 'updateStatus']);
