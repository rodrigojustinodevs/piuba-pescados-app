<?php

declare(strict_types=1);

use App\Presentation\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'authenticateUser']);

Route::group(['middleware' => ['auth:api']], function (): void {
    Route::get('/ping', fn (): string => 'pong');
});

Route::prefix('admin')
    ->middleware(['auth:api', 'api', 'role:admin'])
    ->group(function (): void {
        require base_path('routes/app/admin/company.php');
    });

Route::prefix('company')
    ->middleware(['auth:api', 'api', 'role:admin,company_admin,'])
    ->group(function (): void {
        require base_path('routes/app/company/batche.php');

        require base_path('routes/app/company/biometry.php');

        require base_path('routes/app/company/client.php');

        require base_path('routes/app/company/costAllocation.php');

        require base_path('routes/app/company/feeding.php');

        require base_path('routes/app/company/feedControl.php');

        require base_path('routes/app/company/financialCategory.php');

        require base_path('routes/app/company/financialTransaction.php');

        require base_path('routes/app/company/mortality.php');

        require base_path('routes/app/company/purchase.php');

        require base_path('routes/app/company/sensor.php');

        require base_path('routes/app/company/settlement.php');

        require base_path('routes/app/company/stock.php');

        require base_path('routes/app/company/supplier.php');

        require base_path('routes/app/company/transfer.php');

        require base_path('routes/app/company/tank.php');

        require base_path('routes/app/company/waterQuality.php');
    });
