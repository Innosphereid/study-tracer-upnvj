<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    if (Auth::user()->role === 'superadmin') {
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
    if (Auth::check()) {
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

// Questionnaire Routes
Route::prefix('kuesioner')->middleware(['auth'])->group(function () {
    // Menampilkan daftar kuesioner
    Route::get('/', 'App\Http\Controllers\Questionnaire\QuestionnaireController@index')->name('questionnaires.index');
    
    // Membuat kuesioner baru
    Route::get('/create', 'App\Http\Controllers\Questionnaire\QuestionnaireController@create')->name('questionnaires.create');
    Route::post('/', 'App\Http\Controllers\Questionnaire\QuestionnaireController@store')->name('questionnaires.store');
    
    // Menampilkan, mengedit, dan menghapus kuesioner
    Route::get('/{id}', 'App\Http\Controllers\Questionnaire\QuestionnaireController@show')->name('questionnaires.show');
    Route::get('/{id}/edit', 'App\Http\Controllers\Questionnaire\QuestionnaireController@edit')->name('questionnaires.edit');
    Route::put('/{id}', 'App\Http\Controllers\Questionnaire\QuestionnaireController@update')->name('questionnaires.update');
    Route::delete('/{id}', 'App\Http\Controllers\Questionnaire\QuestionnaireController@destroy')->name('questionnaires.destroy');
    
    // Melihat preview kuesioner
    Route::get('/preview', 'App\Http\Controllers\Questionnaire\PreviewController@index')->name('preview.index');
    
    // Mempublikasikan kuesioner
    Route::post('/{id}/publish', 'App\Http\Controllers\Questionnaire\QuestionnaireController@publish')->name('questionnaires.publish');
    
    // Menutup kuesioner
    Route::post('/{id}/close', 'App\Http\Controllers\Questionnaire\QuestionnaireController@close')->name('questionnaires.close');
    
    // Menduplikasi kuesioner
    Route::post('/{id}/clone', 'App\Http\Controllers\Questionnaire\QuestionnaireController@clone')->name('questionnaires.clone');
    
    // Mengelola respons
    Route::get('/{questionnaireId}/responses', 'App\Http\Controllers\Questionnaire\ResponseController@index')->name('questionnaires.responses.index');
    Route::get('/{questionnaireId}/responses/{responseId}', 'App\Http\Controllers\Questionnaire\ResponseController@show')->name('questionnaires.responses.show');
    Route::delete('/{questionnaireId}/responses/{responseId}', 'App\Http\Controllers\Questionnaire\ResponseController@destroy')->name('questionnaires.responses.destroy');
    Route::get('/{questionnaireId}/responses/export', 'App\Http\Controllers\Questionnaire\ResponseController@export')->name('questionnaires.responses.export');
    Route::get('/{questionnaireId}/statistics', 'App\Http\Controllers\Questionnaire\ResponseController@statistics')->name('questionnaires.statistics');
    
    // Mengelola answer details
    Route::get('/answer-details/response/{responseId}', 'App\Http\Controllers\Questionnaire\AnswerDetailController@getByResponse')
        ->name('answer-details.by-response');
        
    Route::get('/answer-details/question/{questionId}', 'App\Http\Controllers\Questionnaire\AnswerDetailController@getByQuestion')
        ->name('answer-details.by-question');
        
    Route::get('/answer-details/questionnaire/{questionnaireId}', 'App\Http\Controllers\Questionnaire\AnswerDetailController@getByQuestionnaire')
        ->name('answer-details.by-questionnaire');
        
    Route::get('/answer-details/response/{responseId}/question/{questionId}', 'App\Http\Controllers\Questionnaire\AnswerDetailController@getByResponseAndQuestion')
        ->name('answer-details.by-response-and-question');
        
    // File Upload Question Routes
    Route::prefix('file-upload')->group(function () {
        Route::post('/store', 'App\Http\Controllers\Questionnaire\FileUploadQuestionController@store')
            ->name('file-upload.store');
        Route::put('/{id}', 'App\Http\Controllers\Questionnaire\FileUploadQuestionController@update')
            ->name('file-upload.update');
        Route::post('/validate', 'App\Http\Controllers\Questionnaire\FileUploadQuestionController@validateFileType')
            ->name('file-upload.validate');
    });
});

// Endpoint untuk alumni (tidak perlu login)
Route::prefix('kuesioner')->group(function () {
    // New route: Access questionnaire by slug
    Route::get('/{slug}', 'App\Http\Controllers\Questionnaire\FormController@show')
        ->where('slug', '[a-z0-9-]+')
        ->name('form.show');
    
    // Submit jawaban kuesioner
    Route::post('/submit', 'App\Http\Controllers\Questionnaire\FormController@store')
        ->name('form.submit');
    
    // Thank you page after submission
    Route::get('/{slug}/thank-you', 'App\Http\Controllers\Questionnaire\FormController@thankYou')
        ->name('form.thank-you');
});

Route::get('/vue-test', function() {
    return view('vue-test');
})->name('vue-test');