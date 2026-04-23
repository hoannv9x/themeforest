<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminUserActionController;
use App\Http\Controllers\Api\ApiSubscriptionController;
use App\Http\Controllers\Api\ApiWebhookController;
use App\Http\Controllers\Api\NumberStatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Middleware\VerifyUserVip;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/payments/bank-notify', [PaymentController::class, 'notifyBank']);
    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/results/{date}', [ResultController::class, 'show']);

    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/stats/{number}', [StatsController::class, 'detail']);
    Route::get('/number/most-frequent', [NumberStatController::class, 'index']);

    Route::get('/predictions', [PredictionController::class, 'today']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/payments/plans', [PaymentController::class, 'plans']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}/status', [PaymentController::class, 'status']);
        Route::get('/api/subscription', [ApiSubscriptionController::class, 'mySubscription']);
        Route::get('/api/webhooks', [ApiWebhookController::class, 'index']);
        Route::post('/api/webhooks', [ApiWebhookController::class, 'store']);
        Route::put('/api/webhooks/{apiWebhook}', [ApiWebhookController::class, 'update']);
        Route::delete('/api/webhooks/{apiWebhook}', [ApiWebhookController::class, 'destroy']);
        Route::post('/payments/{payment}/paid', [PaymentController::class, 'markPaid']);
    });

    Route::middleware([VerifyUserVip::class, 'auth:sanctum'])->group(function () {
        Route::get('/vip/predictions', [PredictionController::class, 'todayVip']);
    });

    Route::middleware('signed')->group(function () {
        Route::get('/admin/users/{user}/role', [AdminUserActionController::class, 'updateRole'])->name('admin.user.update-role');
        Route::get('/admin/users/{user}/permission', [AdminUserActionController::class, 'updatePermission'])->name('admin.user.update-permission');
    });
});
