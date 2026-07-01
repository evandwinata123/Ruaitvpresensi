<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

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
    Route::get('/attendance/history/{bulan}/{tahun}', [AttendanceController::class, 'historyPage'])->name('attendance.history.page');
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

// Admin routes (protected)
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Manajemen Karyawan
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [AdminController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{id}/edit', [AdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{id}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [AdminController::class, 'deleteEmployee'])->name('employees.delete');

    // Review Presensi
    Route::get('/review', [AdminController::class, 'reviewPresensi'])->name('review');
    Route::post('/review/{id}/approve', [AdminController::class, 'approveAttendance'])->name('review.approve');
    Route::post('/review/{id}/reject', [AdminController::class, 'rejectAttendance'])->name('review.reject');

    // Rekap Absensi
    Route::get('/rekap', [AdminController::class, 'rekapAbsensi'])->name('rekap');
    Route::get('/rekap/export', [AdminController::class, 'exportRekap'])->name('rekap.export');

    // Manajemen Perizinan
    Route::get('/leaves', [AdminController::class, 'leaveRequests'])->name('leaves');
    Route::post('/leaves/{id}/approve', [AdminController::class, 'approveLeave'])->name('leaves.approve');
    Route::post('/leaves/{id}/reject', [AdminController::class, 'rejectLeave'])->name('leaves.reject');

    // Laporan Detail
    Route::get('/laporan', [AdminController::class, 'laporanDetail'])->name('laporan');
});
