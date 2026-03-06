<?php

use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\FacultyController;
use App\Http\Controllers\Dashboard\HomeController;
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
    Route::middleware('dashboard')->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Admin-only
        Route::middleware('admin')->group(function () {
            Route::resource('faculties', FacultyController::class)->except(['show']);

            Route::get('/departments/create/academic',   [DepartmentController::class, 'createAcademic'])->name('departments.create.academic');
            Route::get('/departments/create/managerial', [DepartmentController::class, 'createManagerial'])->name('departments.create.managerial');
            Route::resource('departments', DepartmentController::class)->except(['show', 'create']);
        });

        // Faculty show — accessible to all dashboard users (admin + employee)
        Route::get('/faculties/{faculty}', [FacultyController::class, 'show'])->name('faculties.show');
    });

});
