<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — rate-limited to prevent brute-force
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:api.login')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware('throttle:api.password')->group(function () {
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [PasswordResetController::class, 'reset']);
});

/*
|--------------------------------------------------------------------------
| Protected routes — authenticated users (60 req/min)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth utilities
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // ── Student portal ───────────────────────────────────────────────────
    Route::middleware('api.role:student')->prefix('student')->group(function () {
        Route::get('/profile',     [StudentController::class, 'profile']);
        Route::get('/enrollments', [StudentController::class, 'enrollments']);
    });

    // ── Professor portal ─────────────────────────────────────────────────
    Route::middleware('api.role:professor')->prefix('professor')->group(function () {
        Route::get('/profile',  [ProfessorController::class, 'profile']);
        Route::get('/sections', [ProfessorController::class, 'sections']);
    });

    // ── Announcements (any authenticated user) ───────────────────────────
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements/{id}/read', [AnnouncementController::class, 'markRead']);
});
