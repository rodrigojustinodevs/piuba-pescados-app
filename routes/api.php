<?php

declare(strict_types=1);

use App\Presentation\Controllers\BatcheController;
use App\Presentation\Controllers\BiometryController;
use App\Presentation\Controllers\CompanyController;
use App\Presentation\Controllers\FeedControlController;
use App\Presentation\Controllers\FeedingController;
use App\Presentation\Controllers\MortalityController;
use App\Presentation\Controllers\SensorController;
use App\Presentation\Controllers\SettlementController;
use App\Presentation\Controllers\StockController;
use App\Presentation\Controllers\TankController;
use App\Presentation\Controllers\TransferController;
use App\Presentation\Controllers\WaterQualityController;
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

Route::post('biometry', [BiometryController::class, 'store']);
Route::get('biometries', [BiometryController::class, 'index']);
Route::get('biometry/{id}', [BiometryController::class, 'show']);
Route::put('biometry/{id}', [BiometryController::class, 'update']);
Route::delete('biometry/{id}', [BiometryController::class, 'destroy']);

Route::post('mortality', [MortalityController::class, 'store']);
Route::get('mortalities', [MortalityController::class, 'index']);
Route::get('mortality/{id}', [MortalityController::class, 'show']);
Route::put('mortality/{id}', [MortalityController::class, 'update']);
Route::delete('mortality/{id}', [MortalityController::class, 'destroy']);

Route::post('settlement', [SettlementController::class, 'store']);
Route::get('settlements', [SettlementController::class, 'index']);
Route::get('settlement/{id}', [SettlementController::class, 'show']);
Route::put('settlement/{id}', [SettlementController::class, 'update']);
Route::delete('settlement/{id}', [SettlementController::class, 'destroy']);

Route::post('feeding', [FeedingController::class, 'store']);
Route::get('feedings', [FeedingController::class, 'index']);
Route::get('feeding/{id}', [FeedingController::class, 'show']);
Route::put('feeding/{id}', [FeedingController::class, 'update']);
Route::delete('feeding/{id}', [FeedingController::class, 'destroy']);

Route::post('feed-control', [FeedControlController::class, 'store']);
Route::get('feed-controls', [FeedControlController::class, 'index']);
Route::get('feed-control/{id}', [FeedControlController::class, 'show']);
Route::put('feed-control/{id}', [FeedControlController::class, 'update']);
Route::delete('feed-control/{id}', [FeedControlController::class, 'destroy']);

Route::post('stock', [StockController::class, 'store']);
Route::get('stocks', [StockController::class, 'index']);
Route::get('stock/{id}', [StockController::class, 'show']);
Route::put('stock/{id}', [StockController::class, 'update']);
Route::delete('stock/{id}', [StockController::class, 'destroy']);

Route::post('sensor', [SensorController::class, 'store']);
Route::get('sensors', [SensorController::class, 'index']);
Route::get('sensor/{id}', [SensorController::class, 'show']);
Route::put('sensor/{id}', [SensorController::class, 'update']);
Route::delete('sensor/{id}', [SensorController::class, 'destroy']);

Route::post('transfer', [TransferController::class, 'store']);
Route::get('transfers', [TransferController::class, 'index']);
Route::get('transfer/{id}', [TransferController::class, 'show']);
Route::put('transfer/{id}', [TransferController::class, 'update']);
Route::delete('transfer/{id}', [TransferController::class, 'destroy']);

Route::post('water-quality', [WaterQualityController::class, 'store']);
Route::get('water-qualities', [WaterQualityController::class, 'index']);
Route::get('water-quality/{id}', [WaterQualityController::class, 'show']);
Route::put('water-quality/{id}', [WaterQualityController::class, 'update']);
Route::delete('water-quality/{id}', [WaterQualityController::class, 'destroy']);
