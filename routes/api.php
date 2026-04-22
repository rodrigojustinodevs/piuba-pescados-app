<?php

declare(strict_types=1);

use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::get('/ping', fn (): string => 'pong');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// ── Autenticado (sem company context) ────────────────────────────────────────
Route::prefix('auth')->name('auth.')->middleware('auth:api')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/switch-company', [AuthController::class, 'switchCompany'])->name('switch-company');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
});

// ── Admin global (sem company context — master_admin only) ───────────────────
Route::prefix('admin')
    ->middleware(['auth:api', 'role:admin,master_admin'])
    ->group(function (): void {
        require base_path('routes/app/admin/company.php');

        require base_path('routes/app/admin/subscription.php');
    });

// ── Rotas com company context ─────────────────────────────────────────────────
// auth:api → company.context → (permission ou role por rota)
Route::middleware(['auth:api', 'company.context'])->group(function (): void {
    // ── Debug (remover em produção) ───────────────────────────────────────────
    Route::get('/debug-context', fn() => response()->json([
        'company_id' => App\Infrastructure\Security\CompanyContext::getCompanyId(),
        'role'       => App\Infrastructure\Security\CompanyContext::getRole(),
        'is_master'  => App\Infrastructure\Security\CompanyContext::isMasterAdmin(),
        'user_id'    => App\Infrastructure\Security\CompanyContext::getUserId(),
    ]));

    // ── Companies ─────────────────────────────────────────────────────────────
    Route::prefix('companies/{companyId}')->group(function (): void {
        Route::get('members', [CompanyController::class, 'members'])
            ->middleware('permission:view-user');

        Route::post('members', [CompanyController::class, 'addMember'])
            ->middleware('permission:create-user,assign-user-role');
    });

    // ── Company (rotas de negócio — role mínima: operator) ────────────────────
    Route::prefix('company')
        ->middleware('role:operator,admin,company_admin,master_admin')
        ->group(function (): void {
            require base_path('routes/app/company/alert.php');

            require base_path('routes/app/company/batch.php');

            require base_path('routes/app/company/biometry.php');

            require base_path('routes/app/company/client.php');

            require base_path('routes/app/company/costAllocation.php');

            require base_path('routes/app/company/dashboard.php');

            require base_path('routes/app/company/feeding.php');

            require base_path('routes/app/company/feedInventory.php');

            require base_path('routes/app/company/financialCategory.php');

            require base_path('routes/app/company/financialTransaction.php');

            require base_path('routes/app/company/harvest.php');

            require base_path('routes/app/company/growthCurve.php');

            require base_path('routes/app/company/mortality.php');

            require base_path('routes/app/company/purchase.php');

            require base_path('routes/app/company/sale.php');

            require base_path('routes/app/company/salesOrder.php');

            require base_path('routes/app/company/sensor.php');

            require base_path('routes/app/company/sensorReading.php');

            require base_path('routes/app/company/stocking.php');

            require base_path('routes/app/company/stock.php');

            require base_path('routes/app/company/stockTransaction.php');

            require base_path('routes/app/company/supplier.php');

            require base_path('routes/app/company/supply.php');

            require base_path('routes/app/company/transfer.php');

            require base_path('routes/app/company/tank.php');

            require base_path('routes/app/company/tankHistory.php');

            require base_path('routes/app/company/stockingHistory.php');

            require base_path('routes/app/company/waterQuality.php');
        });

    // ── Master Admin (bypass total de company) ────────────────────────────────
    Route::middleware('role:master_admin')->prefix('admin')->group(function (): void {
        // Route::apiResource('companies', AdminCompanyController::class);
    });
});
