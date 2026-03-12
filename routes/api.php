<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CourseRatingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\ProfessorGradeController;
use App\Http\Controllers\Api\SectionAnnouncementController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentEnrollmentController;
use App\Http\Controllers\Api\StudentWaitlistController;
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
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);

    // ── Student portal ───────────────────────────────────────────────────
    Route::middleware('api.role:student')->prefix('student')->group(function () {
        Route::get('/profile',     [StudentController::class, 'profile']);
        Route::get('/enrollments', [StudentController::class, 'enrollments']);
        Route::post('/enrollments', [StudentEnrollmentController::class, 'store']);
        Route::delete('/enrollments/{enrollment}', [StudentEnrollmentController::class, 'destroy']);
        Route::get('/grades',      [StudentController::class, 'grades']);
        Route::get('/transcript',  [StudentController::class, 'transcript']);
        Route::get('/schedule',    [StudentController::class, 'schedule']);
        Route::get('/attendance',  [AttendanceController::class, 'studentAttendance']);
        Route::get('/sections/{section}/announcements', [SectionAnnouncementController::class, 'studentIndex']);
        Route::get('/ratings',     [CourseRatingController::class, 'index']);
        Route::post('/ratings',    [CourseRatingController::class, 'store']);
        Route::get('/waitlist',    [StudentWaitlistController::class, 'index']);
        Route::delete('/waitlist/{section}', [StudentWaitlistController::class, 'destroy']);
    });

    // ── Professor portal ─────────────────────────────────────────────────
    Route::middleware('api.role:professor')->prefix('professor')->group(function () {
        Route::get('/profile',  [ProfessorController::class, 'profile']);
        Route::get('/sections', [ProfessorController::class, 'sections']);
        Route::get('/schedule', [ProfessorController::class, 'schedule']);
        Route::get('/sections/{section}/students', [ProfessorController::class, 'sectionStudents']);
        Route::get('/sections/{section}/grades',   [ProfessorGradeController::class, 'index']);
        Route::post('/sections/{section}/grades',  [ProfessorGradeController::class, 'store']);
        Route::get('/sections/{section}/attendance',              [AttendanceController::class, 'index']);
        Route::post('/sections/{section}/attendance',             [AttendanceController::class, 'store']);
        Route::get('/sections/{section}/attendance/{session}',    [AttendanceController::class, 'show']);
        Route::put('/sections/{section}/attendance/{session}',    [AttendanceController::class, 'update']);
        Route::get('/sections/{section}/announcements',           [SectionAnnouncementController::class, 'index']);
        Route::post('/sections/{section}/announcements',          [SectionAnnouncementController::class, 'store']);
        Route::delete('/sections/{section}/announcements/{announcement}', [SectionAnnouncementController::class, 'destroy']);
    });

    // ── Announcements (any authenticated user) ───────────────────────────
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements/{id}/read', [AnnouncementController::class, 'markRead']);

    // ── Notifications (any authenticated user) ───────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
