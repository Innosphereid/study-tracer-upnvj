<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', function () {
    return view('password.request');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    // Implementasi akan dibuat pada backend
    // Untuk sementara, redirect ke halaman verifikasi OTP
    return redirect()->route('password.verify-otp-form', ['email' => $request->email]);
})->middleware('guest')->name('password.email');

Route::get('/verify-otp', function (Request $request) {
    return view('password.verify-otp', ['email' => $request->email]);
})->middleware('guest')->name('password.verify-otp-form');

Route::post('/verify-otp', function (Request $request) {
    // Implementasi akan dibuat pada backend
    // Untuk sementara, redirect ke halaman reset password
    return redirect()->route('password.reset-form', [
        'token' => 'dummy-token',
        'email' => $request->email
    ]);
})->middleware('guest')->name('password.verify-otp');

Route::post('/resend-otp', function (Request $request) {
    // Implementasi akan dibuat pada backend
    return back()->with('status', 'Verification code has been resent to your email.');
})->middleware('guest')->name('password.resend-otp');

Route::get('/reset-password/{token}', function (Request $request, $token) {
    return view('password.reset', [
        'token' => $token,
        'email' => $request->email
    ]);
})->middleware('guest')->name('password.reset-form');

Route::post('/reset-password', function (Request $request) {
    // Implementasi akan dibuat pada backend
    return redirect()->route('password.success');
})->middleware('guest')->name('password.update');

Route::get('/reset-success', function () {
    return view('password.success');
})->middleware('guest')->name('password.success');

// SuperAdmin Dashboard
Route::get('/superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:superadmin'])
    ->name('superadmin.dashboard');

// Admin Dashboard
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,superadmin'])
    ->name('admin.dashboard');

// Fallback route
Route::fallback(function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }
        return redirect()->route('admin.dashboard');
    }
    
    return redirect()->route('login');
});

// Email Preview Routes (for development only)
Route::prefix('email-preview')->name('email.preview.')->group(function () {
    // Reset Password Request Email Preview
    Route::get('/reset-password-request', function () {
        return view('emails.password.reset-request', [
            'name' => 'Budi Santoso',
            'otp' => '123456'
        ]);
    })->name('reset-request');

    // Reset Password Success Email Preview
    Route::get('/reset-password-success', function () {
        return view('emails.password.reset-success', [
            'name' => 'Budi Santoso',
            'reset_time' => now()
        ]);
    })->name('reset-success');
});