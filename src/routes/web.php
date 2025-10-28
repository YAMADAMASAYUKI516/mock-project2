<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;

// 一般ユーザー向け：認証前
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// 一般ユーザー向け：認証後
Route::middleware(['auth'])->group(function () {
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('index', [AttendanceController::class, 'index'])->name('index');
        Route::post('start', [AttendanceController::class, 'start'])->name('start');
        Route::post('end', [AttendanceController::class, 'end'])->name('end');
        Route::post('break/start', [AttendanceController::class, 'breakStart'])->name('break.start');
        Route::post('break/end', [AttendanceController::class, 'breakEnd'])->name('break.end');
        Route::get('list', [AttendanceController::class, 'list'])->name('list');
        Route::get('detail/{id}', [AttendanceController::class, 'detail'])->name('detail');
        Route::get('detail', [AttendanceController::class, 'detailByDate'])->name('detail_by_date');
        Route::post('request/{id}', [AttendanceController::class, 'request'])->name('request');
    });

    Route::get('/request/list', [RequestController::class, 'list'])->name('request.list');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // メール認証
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/attendance/index');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware(['throttle:6,1'])->name('verification.resend');
});

// 管理者用
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('attendance.detail');
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])->name('attendance.staff');

        Route::get('/request/list', [AdminRequestController::class, 'list'])->name('request.list');
        Route::post('/request/approve/{id}', [AdminRequestController::class, 'approve'])->name('request.approve');

        Route::get('/staff/list', [AdminStaffController::class, 'list'])->name('staff.list');

        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    });
});
