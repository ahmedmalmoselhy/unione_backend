<?php

use App\Http\Controllers\Admin\QueueHealthController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CourseRatingController;
use App\Http\Controllers\Api\BroadcastingChannelController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\ProfessorGradeController;
use App\Http\Controllers\Api\SectionAnnouncementController;
use App\Http\Controllers\Api\SectionExamScheduleController;
use App\Http\Controllers\Api\SectionGroupProjectController;
use App\Http\Controllers\Api\SectionTeachingAssistantController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentEnrollmentController;
use App\Http\Controllers\Api\StudentWaitlistController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Health Check — publicly accessible
|--------------------------------------------------------------------------
*/
Route::get('/health', HealthController::class);

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
| All API routes are versioned under /api/v1/ for future compatibility.
| The /api/ prefix without version is deprecated and will redirect to v1.
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

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

        // ── Token / session management ────────────────────────────────────────
        Route::get('/auth/tokens', [TokenController::class, 'index']);
        Route::delete('/auth/tokens', [TokenController::class, 'destroyAll']);
        Route::delete('/auth/tokens/{tokenId}', [TokenController::class, 'destroy']);

        // ── Student portal ───────────────────────────────────────────────────
        Route::middleware('api.role:student')->prefix('student')->group(function () {
            Route::get('/profile',     [StudentController::class, 'profile']);
            Route::get('/enrollments', [StudentController::class, 'enrollments']);
            Route::post('/enrollments', [StudentEnrollmentController::class, 'store'])->middleware('throttle:api.enroll');
            Route::delete('/enrollments/{enrollment}', [StudentEnrollmentController::class, 'destroy'])->middleware('throttle:api.enroll');
            Route::get('/grades',          [StudentController::class, 'grades']);
            Route::get('/transcript',      [StudentController::class, 'transcript']);
            Route::get('/transcript/pdf',  [StudentController::class, 'transcriptPdf']);
            Route::get('/academic-history',[StudentController::class, 'academicHistory']);
            Route::get('/schedule',        [StudentController::class, 'schedule']);
            Route::get('/schedule/ics',    [StudentController::class, 'scheduleIcs']);
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
            Route::post('/sections/{section}/grades',  [ProfessorGradeController::class, 'store'])->middleware('throttle:api.grade');
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

        // ── Broadcasting / Real-time ─────────────────────────────────────────
        Route::get('/broadcasting/auth', [BroadcastingChannelController::class, 'auth']);
        Route::get('/broadcasting/config', [BroadcastingChannelController::class, 'config']);

        // ── Webhook management (admin only) ───────────────────────────────────
        Route::middleware('api.role:admin,faculty_admin,department_admin')->prefix('admin')->group(function () {
            Route::get('/sections/{section}/teaching-assistants', [SectionTeachingAssistantController::class, 'index']);
            Route::post('/sections/{section}/teaching-assistants', [SectionTeachingAssistantController::class, 'store']);
            Route::delete('/sections/{section}/teaching-assistants/{sectionTeachingAssistant}', [SectionTeachingAssistantController::class, 'destroy']);
            Route::get('/sections/{section}/exam-schedule', [SectionExamScheduleController::class, 'show']);
            Route::post('/sections/{section}/exam-schedule', [SectionExamScheduleController::class, 'store']);
            Route::patch('/sections/{section}/exam-schedule', [SectionExamScheduleController::class, 'update']);
            Route::post('/sections/{section}/exam-schedule/publish', [SectionExamScheduleController::class, 'publish']);
            Route::get('/sections/{section}/group-projects', [SectionGroupProjectController::class, 'index']);
            Route::post('/sections/{section}/group-projects', [SectionGroupProjectController::class, 'store']);
            Route::patch('/sections/{section}/group-projects/{groupProject}', [SectionGroupProjectController::class, 'update']);
            Route::delete('/sections/{section}/group-projects/{groupProject}', [SectionGroupProjectController::class, 'destroy']);
            Route::post('/sections/{section}/group-projects/{groupProject}/members', [SectionGroupProjectController::class, 'storeMember']);
            Route::delete('/sections/{section}/group-projects/{groupProject}/members/{groupProjectMember}', [SectionGroupProjectController::class, 'destroyMember']);
            Route::get('/webhooks', [WebhookController::class, 'index']);
            Route::post('/webhooks', [WebhookController::class, 'store']);
            Route::patch('/webhooks/{webhook}', [WebhookController::class, 'update']);
            Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy']);
            Route::get('/webhooks/{webhook}/deliveries', [WebhookController::class, 'deliveries']);

            // Queue monitoring (admin only)
            Route::get('/queue/health', [QueueHealthController::class, 'health']);
            Route::get('/queue/failed', [QueueHealthController::class, 'failedJobs']);
            Route::delete('/queue/failed/clear', [QueueHealthController::class, 'clearFailedJobs']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Backward Compatibility — redirect old /api/* routes to /api/v1/*
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    $path = request()->path();
    $newPath = 'v1/' . $path;
    $url = str_replace($path, $newPath, request()->fullUrl());

    return response()->json([
        'message' => 'API versioning is now required.',
        'redirect' => $url,
        'documentation' => config('app.url') . '/docs/api',
    ], 410)->header('X-API-Deprecation', 'Use /api/v1/ instead');
});
