<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\VocabularyController;
use App\Http\Controllers\Api\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('profile')
        ]);
    });

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);

    // Courses & Progress
    Route::get('/my-courses', [CourseController::class, 'myCourses']);
    Route::get('/lessons/{slug}', [CourseController::class, 'lesson']);
    Route::post('/lessons/{id}/complete', [CourseController::class, 'completeLesson']);

    // Vocabulary
    Route::get('/vocabularies', [VocabularyController::class, 'index']);
    Route::get('/vocabularies/{slug}', [VocabularyController::class, 'show']);
    Route::post('/vocabularies/{id}/favorite', [VocabularyController::class, 'toggleFavorite']);
    Route::post('/vocabularies/{id}/status', [VocabularyController::class, 'updateStatus']);

    // Quizzes
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes/{id}/submit', [QuizController::class, 'submit']);
    Route::get('/quiz-results', [QuizController::class, 'results']);
});
