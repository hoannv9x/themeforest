<?php

use App\Http\Controllers\Api\NumberStatController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/results/{date}', [ResultController::class, 'show']);

    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/stats/{number}', [StatsController::class, 'detail']);
    Route::get('/number/most-frequent', [NumberStatController::class, 'index']);

    Route::get('/predictions', [PredictionController::class, 'today']);

    Route::middleware('auth:sanctum')->get('/vip/predictions', function () {
        return response()->json(['vip' => true]);
    });
});
