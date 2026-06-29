<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Login page
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');

// Password reset routes
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Attendance routes (protected)
Route::middleware('auth')->group(function () {
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/attendance/status', [AttendanceController::class, 'status'])->name('attendance.status');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
});

// Leave routes (protected)
Route::middleware('auth')->group(function () {
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Employee dashboard (protected)
Route::get('/dashboard', [DashboardController::class, 'employee'])->middleware('auth')->name('dashboard');

// Profile routes (protected)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Admin dashboard (protected)
Route::get('/admin/dashboard', function () {
    return view('dashboard.admin');
})->middleware('auth')->middleware('can:admin')->name('admin.dashboard');
