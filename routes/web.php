<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
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