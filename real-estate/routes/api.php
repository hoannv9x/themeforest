<?php

use App\Http\Controllers\AdminCityController;
use App\Http\Controllers\AdminDistrictController;
use App\Http\Controllers\AdminPropertyTypeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminUploadController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminPropertyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{slug}', [PropertyController::class, 'show']);
Route::get('/properties/{slug}/related', [PropertyController::class, 'related']);
Route::get('/search', [PropertyController::class, 'search']);
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/cities', [AdminCityController::class, 'index']);
Route::get('/property-types', [AdminPropertyTypeController::class, 'index']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('properties', PropertyController::class)->except(['index', 'show']); // CRUD for properties
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{property_id}', [FavoriteController::class, 'destroy']);
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/properties/{property}/nearby-places', [PropertyController::class, 'nearbyPlaces']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard Stats
    Route::get('/dashboard-stats', [AdminController::class, 'dashboardStats']);

    // User Management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

    // Uploads
    Route::post('/upload', [AdminUploadController::class, 'upload']);
    Route::delete('/upload', [AdminUploadController::class, 'revert']);

    // Property Management
    Route::get('/properties', [AdminPropertyController::class, 'index']);
    Route::get('/properties/{property}', [AdminPropertyController::class, 'show']);
    Route::put('/properties/{property}/approve', [AdminPropertyController::class, 'approve']);
    Route::put('/properties/{property}', [AdminPropertyController::class, 'update']);
    Route::delete('/properties/{property}', [AdminPropertyController::class, 'destroy']);

    // Property Type Management
    Route::apiResource('property-types', AdminPropertyTypeController::class);

    // Location Management (Cities, Districts)
    Route::apiResource('cities', AdminCityController::class);
    Route::apiResource('districts', AdminDistrictController::class);
});
