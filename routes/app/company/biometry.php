<?php

declare(strict_types=1);

use App\Presentation\Controllers\BiometryController;
use Illuminate\Support\Facades\Route;

Route::post('biometry', [BiometryController::class, 'store'])
    ->middleware('permission:create-biometry');

Route::get('biometries', [BiometryController::class, 'index'])
    ->middleware('permission:view-biometry');

Route::get('biometry/{id}', [BiometryController::class, 'show'])
    ->middleware('permission:view-biometry');

Route::put('biometry/{id}', [BiometryController::class, 'update'])
    ->middleware('permission:update-biometry');

Route::delete('biometry/{id}', [BiometryController::class, 'destroy'])
    ->middleware('permission:delete-biometry');
