<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminUserActionController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminResultController;
use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AdminCouponController;
use App\Http\Controllers\Api\AdminLogController;
use App\Http\Controllers\Api\AdminJobController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\ApiSubscriptionController;
use App\Http\Controllers\Api\ApiWebhookController;
use App\Http\Controllers\Api\NumberStatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\MiniGameController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\VipController;
use App\Http\Middleware\VerifyAdmin;
use App\Http\Middleware\VerifyOnlyAdmin;
use App\Http\Middleware\VerifyUserVip;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/email/request-code', [EmailVerificationController::class, 'requestCode']);
    Route::post('/auth/google', [GoogleAuthController::class, 'login']);
    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/results/{date}', [ResultController::class, 'show']);
    
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/stats/{number}', [StatsController::class, 'detail']);
    Route::get('/number/most-frequent', [NumberStatController::class, 'index']);
    
    Route::get('/predictions', [PredictionController::class, 'today']);
    Route::get('/predictions/yesterday', [PredictionController::class, 'yesterday']);
    Route::get('/mini-game', [MiniGameController::class, 'index']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/payments/bank-notify', [PaymentController::class, 'notifyBank']);
        Route::get('/me', [AuthController::class, 'user']);
        Route::post('/me/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/coupons/my', [CouponController::class, 'my']);
        Route::get('/referral/me', [ReferralController::class, 'me']);
        Route::post('/referral/attach', [ReferralController::class, 'attach']);
        Route::get('/payments/plans', [PaymentController::class, 'plans']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/history', [PaymentController::class, 'history']);
        Route::get('/payments/{payment}/status', [PaymentController::class, 'status']);
        Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel']);
        Route::get('/api/subscription', [ApiSubscriptionController::class, 'mySubscription']);
        Route::get('/api/webhooks', [ApiWebhookController::class, 'index']);
        Route::post('/api/webhooks', [ApiWebhookController::class, 'store']);
        Route::put('/api/webhooks/{apiWebhook}', [ApiWebhookController::class, 'update']);
        Route::delete('/api/webhooks/{apiWebhook}', [ApiWebhookController::class, 'destroy']);
        Route::post('/payments/{payment}/paid', [PaymentController::class, 'markPaid']);
        Route::get('/mini-game/me', [MiniGameController::class, 'me']);
        Route::post('/mini-game/predict', [MiniGameController::class, 'predict']);
        Route::post('/mini-game/payout-request', [MiniGameController::class, 'submitPayoutRequest']);
        
        Route::get('/vip/status', [VipController::class, 'status']);
        Route::get('/vip/upsell', [VipController::class, 'upsell']);
        Route::post('/vip/start-trial', [VipController::class, 'startTrial']);
    });

    Route::middleware([VerifyUserVip::class, 'auth:sanctum'])->group(function () {
        Route::get('/vip/predictions', [PredictionController::class, 'todayVip']);
        Route::get('/vip/predictions/yesterday', [PredictionController::class, 'yesterdayVip']);
        Route::get('/vip/results', [ResultController::class, 'vipIndex']);
        Route::get('/vip/results/{date}', [ResultController::class, 'vipShow']);
        Route::get('/vip/stats', [StatsController::class, 'vipIndex']);
        Route::get('/vip/stats/{number}', [StatsController::class, 'vipDetail']);
        Route::get('/vip/number/most-frequent', [NumberStatController::class, 'vipIndex']);
    });

    Route::middleware([VerifyAdmin::class, 'auth:sanctum'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);

        Route::get('/results', [AdminResultController::class, 'index']);
        Route::get('/results/by-date/{date}', [AdminResultController::class, 'showByDate']);
        Route::put('/results/by-date/{date}', [AdminResultController::class, 'upsertByDate']);

        Route::get('/payments', [AdminPaymentController::class, 'index']);
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show']);
        Route::post('/payments/{payment}/approve', [AdminPaymentController::class, 'approve']);
        Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject']);

        Route::get('/coupons', [AdminCouponController::class, 'index']);
        Route::post('/coupons', [AdminCouponController::class, 'store']);
        Route::get('/coupons/{coupon}', [AdminCouponController::class, 'show']);
        Route::put('/coupons/{coupon}', [AdminCouponController::class, 'update']);

        Route::get('/logs', [AdminLogController::class, 'index'])->middleware(VerifyOnlyAdmin::class);
        Route::get('/logs/{date}', [AdminLogController::class, 'show'])->middleware(VerifyOnlyAdmin::class);
        Route::get('/logs/{date}/download', [AdminLogController::class, 'download'])->middleware(VerifyOnlyAdmin::class);

        Route::post('/jobs/run-daily-pipeline', [AdminJobController::class, 'runDailyPipeline'])
            ->middleware(VerifyOnlyAdmin::class);
    });

    Route::middleware('signed')->group(function () {
        Route::get('/admin/users/{user}/role', [AdminUserActionController::class, 'updateRole'])->name('admin.user.update-role');
        Route::get('/admin/users/{user}/permission', [AdminUserActionController::class, 'updatePermission'])->name('admin.user.update-permission');
    });
});
