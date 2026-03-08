<?php

use App\Http\Controllers\Dashboard\AcademicTermController;
use App\Http\Controllers\Dashboard\AdminAssignmentController;
use App\Http\Controllers\Dashboard\AnnouncementController;
use App\Http\Controllers\Dashboard\AuditLogController;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\ChangePasswordController;
use App\Http\Controllers\Dashboard\CourseController;
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\DepartmentHeadController;
use App\Http\Controllers\Dashboard\EmployeeController;
use App\Http\Controllers\Dashboard\EnrollmentController;
use App\Http\Controllers\Dashboard\FacultyController;
use App\Http\Controllers\Dashboard\GradeController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\ProfessorController;
use App\Http\Controllers\Dashboard\SectionController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\UniversityController;
use App\Http\Controllers\Dashboard\UniversityVicePresidentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

    // Protected by session auth + role check
    Route::middleware(['dashboard', 'force.password'])->group(function () {
        Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
        Route::put('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');

        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Admin-only (system-level)
        Route::middleware('admin')->group(function () {
            Route::resource('faculties', FacultyController::class)->except(['show']);

            Route::resource('academic-terms', AcademicTermController::class);

            Route::get('/university', [UniversityController::class, 'show'])->name('university.show');
            Route::get('/university/edit', [UniversityController::class, 'edit'])->name('university.edit');
            Route::put('/university', [UniversityController::class, 'update'])->name('university.update');

            Route::resource('university/vice-presidents', UniversityVicePresidentController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->names('university.vice-presidents');

            // Assign faculty admin (system admin only)
            Route::get('/faculties/{faculty}/assign-admin', [AdminAssignmentController::class, 'editFacultyAdmin'])->name('faculties.assign-admin');
            Route::post('/faculties/{faculty}/assign-admin', [AdminAssignmentController::class, 'assignFacultyAdmin'])->name('faculties.assign-admin.store');
            Route::delete('/faculties/{faculty}/assign-admin', [AdminAssignmentController::class, 'revokeFacultyAdmin'])->name('faculties.assign-admin.revoke');

            // Audit log (system admin only)
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });

        // Scoped admin (system admin + faculty admin + department admin)
        Route::middleware('scoped.admin')->group(function () {
            Route::get('/departments/create/academic',   [DepartmentController::class, 'createAcademic'])->name('departments.create.academic');
            Route::get('/departments/create/managerial', [DepartmentController::class, 'createManagerial'])->name('departments.create.managerial');
            Route::resource('departments', DepartmentController::class)->except(['create']);

            Route::resource('professors', ProfessorController::class);

            Route::resource('employees', EmployeeController::class);

            Route::resource('courses', CourseController::class);

            Route::resource('sections', SectionController::class);

            Route::resource('students', StudentController::class);
            Route::post('/students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer');

            Route::resource('enrollments', EnrollmentController::class);

            Route::resource('grades', GradeController::class);

            Route::resource('announcements', AnnouncementController::class);

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
    });

});
