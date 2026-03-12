<?php

use App\Http\Controllers\Dashboard\AcademicTermController;
use App\Http\Controllers\Dashboard\AdminAssignmentController;
use App\Http\Controllers\Dashboard\AnnouncementController;
use App\Http\Controllers\Dashboard\AuditLogController;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\ChangePasswordController;
use App\Http\Controllers\Dashboard\CourseController;
use App\Http\Controllers\Dashboard\DashboardStatsController;
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\DepartmentHeadController;
use App\Http\Controllers\Dashboard\EmployeeController;
use App\Http\Controllers\Dashboard\EnrollmentController;
use App\Http\Controllers\Dashboard\FacultyController;
use App\Http\Controllers\Dashboard\GradeController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\LocaleController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\ProfessorController;
use App\Http\Controllers\Dashboard\SectionController;
use App\Http\Controllers\Dashboard\ScheduleController;
use App\Http\Controllers\Dashboard\RatingController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\UniversityController;
use App\Http\Controllers\Dashboard\UniversityVicePresidentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal routes (students, professors, employees)
|--------------------------------------------------------------------------
*/

// Portal login/logout — no auth required
Route::get('/', [\App\Http\Controllers\Portal\AuthController::class, 'showLogin'])->name('portal.login');
Route::post('/login', [\App\Http\Controllers\Portal\AuthController::class, 'login'])->name('portal.login.post');
Route::post('/logout', [\App\Http\Controllers\Portal\AuthController::class, 'logout'])->name('portal.logout');
Route::post('/locale', [LocaleController::class, 'store'])->name('portal.locale');

// Protected portal routes
Route::middleware('portal')->group(function () {

    Route::get('/home', [\App\Http\Controllers\Portal\HomeController::class, 'index'])->name('portal.home');
    Route::get('/profile', [\App\Http\Controllers\Portal\ProfileController::class, 'show'])->name('portal.profile');
    Route::patch('/profile', [\App\Http\Controllers\Portal\ProfileController::class, 'update'])->name('portal.profile.update');
    Route::get('/schedule', [\App\Http\Controllers\Portal\ScheduleController::class, 'index'])->name('portal.schedule');

    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\Portal\AnnouncementController::class, 'index'])->name('portal.announcements.index');
    Route::post('/announcements/{id}/read', [\App\Http\Controllers\Portal\AnnouncementController::class, 'markRead'])->name('portal.announcements.read');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Portal\NotificationController::class, 'index'])->name('portal.notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Portal\NotificationController::class, 'markAllRead'])->name('portal.notifications.read-all');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Portal\NotificationController::class, 'markRead'])->name('portal.notifications.read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Portal\NotificationController::class, 'destroy'])->name('portal.notifications.destroy');

    // Student: courses & grades
    Route::get('/courses', [\App\Http\Controllers\Portal\Student\EnrollmentController::class, 'index'])->name('portal.enrollments.index');
    Route::get('/courses/enroll', [\App\Http\Controllers\Portal\Student\EnrollmentController::class, 'create'])->name('portal.enrollments.create');
    Route::post('/courses', [\App\Http\Controllers\Portal\Student\EnrollmentController::class, 'store'])->name('portal.enrollments.store');
    Route::delete('/courses/{enrollment}', [\App\Http\Controllers\Portal\Student\EnrollmentController::class, 'destroy'])->name('portal.enrollments.destroy');
    Route::get('/grades', [\App\Http\Controllers\Portal\Student\GradeController::class, 'index'])->name('portal.grades');

    // Professor: sections & grading
    Route::get('/sections', [\App\Http\Controllers\Portal\Professor\SectionController::class, 'index'])->name('portal.sections.index');
    Route::get('/sections/{section}', [\App\Http\Controllers\Portal\Professor\SectionController::class, 'show'])->name('portal.sections.show');
    Route::post('/sections/{section}/grades', [\App\Http\Controllers\Portal\Professor\SectionController::class, 'postGrade'])->name('portal.sections.grade');

    // Professor: attendance
    Route::get('/sections/{section}/attendance', [\App\Http\Controllers\Portal\Professor\AttendanceController::class, 'index'])->name('portal.attendance.index');
    Route::post('/sections/{section}/attendance', [\App\Http\Controllers\Portal\Professor\AttendanceController::class, 'store'])->name('portal.attendance.store');
    Route::get('/sections/{section}/attendance/{session}', [\App\Http\Controllers\Portal\Professor\AttendanceController::class, 'show'])->name('portal.attendance.show');
    Route::put('/sections/{section}/attendance/{session}', [\App\Http\Controllers\Portal\Professor\AttendanceController::class, 'update'])->name('portal.attendance.update');

    // Professor: section announcements
    Route::get('/sections/{section}/announcements', [\App\Http\Controllers\Portal\Professor\SectionAnnouncementController::class, 'index'])->name('portal.section-announcements.index');
    Route::post('/sections/{section}/announcements', [\App\Http\Controllers\Portal\Professor\SectionAnnouncementController::class, 'store'])->name('portal.section-announcements.store');
    Route::delete('/sections/{section}/announcements/{announcement}', [\App\Http\Controllers\Portal\Professor\SectionAnnouncementController::class, 'destroy'])->name('portal.section-announcements.destroy');

    // Student: attendance
    Route::get('/attendance', [\App\Http\Controllers\Portal\Student\AttendanceController::class, 'index'])->name('portal.student.attendance');

    // Student: ratings
    Route::get('/ratings', [\App\Http\Controllers\Portal\Student\RatingController::class, 'index'])->name('portal.ratings.index');
    Route::post('/ratings', [\App\Http\Controllers\Portal\Student\RatingController::class, 'store'])->name('portal.ratings.store');
});

/*
|--------------------------------------------------------------------------
| Dashboard routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')->name('dashboard.')->group(function () {

    // Guest-only (redirect to home if already authenticated)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Locale switcher — available to any visitor (guest or authenticated)
    Route::post('/locale', [LocaleController::class, 'store'])->name('locale.store');

    // Protected by session auth + role check
    Route::middleware(['dashboard', 'force.password'])->group(function () {
        Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
        Route::put('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');

        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Admin-only (system-level)
        Route::middleware('admin')->group(function () {
            Route::resource('faculties', FacultyController::class)->except(['show']);

            Route::resource('academic-terms', AcademicTermController::class);

            // Assign faculty admin (system admin only)
            Route::get('/faculties/{faculty}/assign-admin', [AdminAssignmentController::class, 'editFacultyAdmin'])->name('faculties.assign-admin');
            Route::post('/faculties/{faculty}/assign-admin', [AdminAssignmentController::class, 'assignFacultyAdmin'])->name('faculties.assign-admin.store');
            Route::delete('/faculties/{faculty}/assign-admin', [AdminAssignmentController::class, 'revokeFacultyAdmin'])->name('faculties.assign-admin.revoke');

            // Audit log (system admin only)
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });

        // University settings (system admin + university admin)
        Route::middleware('university.admin')->group(function () {
            Route::get('/university', [UniversityController::class, 'show'])->name('university.show');
            Route::get('/university/edit', [UniversityController::class, 'edit'])->name('university.edit');
            Route::put('/university', [UniversityController::class, 'update'])->name('university.update');

            Route::resource('university/vice-presidents', UniversityVicePresidentController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->names('university.vice-presidents');
        });

        // Scoped admin (system admin + faculty admin + department admin)
        Route::middleware('scoped.admin')->group(function () {
            Route::get('/departments/create/academic',   [DepartmentController::class, 'createAcademic'])->name('departments.create.academic');
            Route::get('/departments/create/managerial', [DepartmentController::class, 'createManagerial'])->name('departments.create.managerial');
            Route::resource('departments', DepartmentController::class)->except(['create']);

            // Professors (export + import before resource)
            Route::get('/professors/export', [ProfessorController::class, 'export'])->name('professors.export');
            Route::get('/professors/import-template', [ProfessorController::class, 'importTemplate'])->name('professors.import-template');
            Route::post('/professors/import', [ProfessorController::class, 'import'])->name('professors.import');
            Route::resource('professors', ProfessorController::class);

            // Employees (export only)
            Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
            Route::resource('employees', EmployeeController::class);

            Route::resource('courses', CourseController::class);

            Route::resource('sections', SectionController::class);

            Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

            // Statistics API (charts-ready JSON)
            Route::get('/stats', [DashboardStatsController::class, 'index'])->name('stats.index');

            // Students (export + import before resource)
            Route::get('/students/export', [StudentController::class, 'export'])->name('students.export');
            Route::get('/students/import-template', [StudentController::class, 'importTemplate'])->name('students.import-template');
            Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
            Route::resource('students', StudentController::class);
            Route::post('/students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer');
            Route::get('/students/{student}/transcript/pdf', [StudentController::class, 'transcriptPdf'])->name('students.transcript.pdf');

            // Enrollments (export only)
            Route::get('/enrollments/export', [EnrollmentController::class, 'export'])->name('enrollments.export');
            Route::resource('enrollments', EnrollmentController::class);

            // Grades (export + import before resource)
            Route::get('/grades/export', [GradeController::class, 'export'])->name('grades.export');
            Route::get('/grades/import-template', [GradeController::class, 'importTemplate'])->name('grades.import-template');
            Route::post('/grades/import', [GradeController::class, 'import'])->name('grades.import');
            Route::resource('grades', GradeController::class);

            Route::resource('announcements', AnnouncementController::class);

            Route::get('/ratings', [RatingController::class, 'index'])->name('ratings.index');

            // Assign department admin (system admin + faculty admin of that faculty)
            Route::get('/departments/{department}/assign-admin', [AdminAssignmentController::class, 'editDepartmentAdmin'])->name('departments.assign-admin');
            Route::post('/departments/{department}/assign-admin', [AdminAssignmentController::class, 'assignDepartmentAdmin'])->name('departments.assign-admin.store');
            Route::delete('/departments/{department}/assign-admin', [AdminAssignmentController::class, 'revokeDepartmentAdmin'])->name('departments.assign-admin.revoke');

            // Assign department head (system admin + faculty admin)
            Route::get('/departments/{department}/assign-head', [DepartmentHeadController::class, 'edit'])->name('departments.assign-head');
            Route::post('/departments/{department}/assign-head', [DepartmentHeadController::class, 'assign'])->name('departments.assign-head.store');
            Route::delete('/departments/{department}/assign-head', [DepartmentHeadController::class, 'revoke'])->name('departments.assign-head.revoke');
        });

        // Faculty show — accessible to all dashboard users (admin + employee)
        Route::get('/faculties/{faculty}', [FacultyController::class, 'show'])->name('faculties.show');

        // Notifications — accessible to all authenticated dashboard users
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

});
