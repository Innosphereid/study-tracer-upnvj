<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SecurityController;
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

// Password Reset Routes
Route::get('/forgot-password', [ResetPasswordController::class, 'showRequestForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/verify-otp', [ResetPasswordController::class, 'showVerifyOtpForm'])
    ->middleware('guest')
    ->name('password.verify-otp-form');

Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp'])
    ->middleware('guest')
    ->name('password.verify-otp');

Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp'])
    ->middleware('guest')
    ->name('password.resend-otp');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset-form');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/reset-success', [ResetPasswordController::class, 'showSuccessPage'])
    ->middleware('guest')
    ->name('password.success');

// Security Report Routes
Route::get('/security/report', [SecurityController::class, 'showReportForm'])
    ->name('security.report');

Route::post('/security/report', [SecurityController::class, 'submitReport'])
    ->name('security.report.submit');

// Dashboard Routes - Shared route for both admin and superadmin
Route::get('/dashboard', function () {
    if (auth()->user()->role === 'superadmin') {
        return app()->make(SuperAdminDashboardController::class)->index();
    }
    return app()->make(AdminDashboardController::class)->index();
})->middleware(['auth'])->name('dashboard');

// Legacy routes that will redirect to the main dashboard
Route::get('/superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:superadmin'])
    ->name('superadmin.dashboard');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,superadmin'])
    ->name('admin.dashboard');

// Fallback route
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
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

// Questionnaire Routes - Accessible by admin and superadmin
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    // Questionnaire CRUD
    Route::get('/questionnaires', [QuestionnaireController::class, 'index'])->name('questionnaires.index');
    Route::get('/questionnaires/create', [QuestionnaireController::class, 'create'])->name('questionnaires.create');
    Route::post('/questionnaires', [QuestionnaireController::class, 'store'])->name('questionnaires.store');
    Route::get('/questionnaires/{id}/edit', [QuestionnaireController::class, 'edit'])->name('questionnaires.edit');
    Route::put('/questionnaires/{id}', [QuestionnaireController::class, 'update'])->name('questionnaires.update');
    Route::delete('/questionnaires/{id}', [QuestionnaireController::class, 'destroy'])->name('questionnaires.destroy');
    
    // Additional questionnaire actions
    Route::get('/questionnaires/{id}/preview', [QuestionnaireController::class, 'preview'])->name('questionnaires.preview');
    Route::post('/questionnaires/{id}/publish', [QuestionnaireController::class, 'publish'])->name('questionnaires.publish');
    Route::post('/questionnaires/{id}/close', [QuestionnaireController::class, 'close'])->name('questionnaires.close');
    Route::post('/questionnaires/{id}/clone', [QuestionnaireController::class, 'clone'])->name('questionnaires.clone');
    Route::post('/questionnaires/{id}/generate-link', [QuestionnaireController::class, 'generateLink'])->name('questionnaires.generate-link');
    Route::get('/questionnaires/{id}/statistics', [QuestionnaireController::class, 'statistics'])->name('questionnaires.statistics');
    
    // Section API routes
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::put('/sections/{id}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{id}', [SectionController::class, 'destroy'])->name('sections.destroy');
    Route::post('/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');
    
    // Question API routes
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
});

// Public questionnaire routes - For respondents (alumni) - No authentication required
Route::get('/kuesioner/{code}', [QuestionnaireController::class, 'showPublic'])->name('questionnaires.public.show');
Route::post('/kuesioner/{code}/start-response', [QuestionnaireController::class, 'startResponse'])->name('questionnaires.public.start-response');
Route::post('/kuesioner/submit-answer', [QuestionnaireController::class, 'submitAnswer'])->name('questionnaires.public.submit-answer');
Route::post('/kuesioner/complete-response', [QuestionnaireController::class, 'completeResponse'])->name('questionnaires.public.complete-response');