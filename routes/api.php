<?php

declare(strict_types=1);

use App\Presentation\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(static function (): void {
    Route::get('/ping', fn (): string => 'pong');

    // Public route — no authentication middleware
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Routes protected by JWT
    Route::middleware('auth:api')->group(static function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])    ->name('me');
    });

    // Refresh uses its own middleware to validate expired token
    Route::middleware('auth:api')
        ->post('/refresh', [AuthController::class, 'refresh'])
        ->name('refresh');
});

Route::prefix('admin')
    ->middleware(['auth:api', 'api', 'role:admin|master_admin'])
    ->group(function (): void {
        require base_path('routes/app/admin/company.php');

        require base_path('routes/app/admin/subscription.php');
    });

Route::prefix('company')
    ->middleware(['auth:api', 'api', 'role:admin|master_admin|company-admin'])
    ->group(function (): void {
        require base_path('routes/app/company/alert.php');

        require base_path('routes/app/company/batch.php');

        require base_path('routes/app/company/biometry.php');

        require base_path('routes/app/company/client.php');

        require base_path('routes/app/company/costAllocation.php');

        require base_path('routes/app/company/feeding.php');

        require base_path('routes/app/company/feedInventory.php');

        require base_path('routes/app/company/financialCategory.php');

        require base_path('routes/app/company/financialTransaction.php');

        require base_path('routes/app/company/harvest.php');

        require base_path('routes/app/company/growthCurve.php');

        require base_path('routes/app/company/mortality.php');

        require base_path('routes/app/company/purchase.php');

        require base_path('routes/app/company/sale.php');

        require base_path('routes/app/company/sensor.php');

        require base_path('routes/app/company/sensorReading.php');

        require base_path('routes/app/company/stocking.php');

        require base_path('routes/app/company/stock.php');

        require base_path('routes/app/company/supplier.php');

        require base_path('routes/app/company/transfer.php');

        require base_path('routes/app/company/tank.php');

        require base_path('routes/app/company/waterQuality.php');
    });
