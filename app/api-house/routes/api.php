<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardingHouseController;
use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::apiResource('boarding-houses', BoardingHouseController::class);
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('tenants', TenantController::class);

        Route::post('meter-readings', [MeterReadingController::class, 'store']);
        Route::post('payments', [PaymentController::class, 'store']);
    });
});

