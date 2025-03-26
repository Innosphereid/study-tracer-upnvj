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

// Questionnaire Routes (Frontend Only)
Route::prefix('kuesioner')->middleware(['auth', 'role:admin,superadmin'])->group(function () {
    // Menampilkan daftar kuesioner
    Route::get('/', function() {
        // Data dummy untuk daftar kuesioner
        // PERUBAHAN: Menggunakan array assosiatif daripada stdClass untuk menghindari masalah konversi
        $questionnaires = collect([
            [
                'id' => 1, // Memastikan id adalah integer atau string, bukan objek
                'title' => 'Tracer Study Alumni Angkatan 2020',
                'slug' => 'tracer-study-alumni-2020',
                'is_published' => true,
                'is_draft' => false,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'responses_count' => 42,
                'updated_at' => now()->subDays(2)
            ],
            [
                'id' => 2, // Memastikan id adalah integer atau string, bukan objek
                'title' => 'Kuesioner Kepuasan Layanan Akademik',
                'slug' => 'kepuasan-layanan-akademik',
                'is_published' => false,
                'is_draft' => true,
                'start_date' => null,
                'end_date' => null,
                'responses_count' => 0,
                'updated_at' => now()->subDays(5)
            ]
        ])->map(function ($item) {
            // Konversi array menjadi objek tetapi dengan id yang bernilai scalar (integer/string)
            return (object) $item;
        });
        
        // Membuat pagination sederhana
        $questionnaires = new \Illuminate\Pagination\LengthAwarePaginator(
            $questionnaires,
            $questionnaires->count(),
            10,
            1
        );
        
        return view('questionnaire.index', compact('questionnaires'));
    })->name('questionnaires.index');
    
    // Membuat kuesioner baru
    Route::get('/create', function() {
        $initialData = [
            'title' => 'Kuesioner Baru',
            'sections' => []
        ];
        
        return view('questionnaire.create', compact('initialData'));
    })->name('questionnaires.create');
    
    // Mengedit kuesioner (parameter bisa apapun, hanya untuk demo)
    Route::get('/{questionnaire}/edit', function($questionnaire) {
        // Data dummy untuk kuesioner
        $questionnaire = [
            'id' => $questionnaire,
            'title' => 'Kuesioner Demo',
            'description' => 'Ini adalah kuesioner contoh untuk pengembangan frontend',
            'slug' => 'kuesioner-demo',
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addMonths(3)->format('Y-m-d'),
            'showProgressBar' => true,
            'showPageNumbers' => true,
            'requiresLogin' => false,
            'sections' => [
                [
                    'id' => 'section1',
                    'title' => 'Informasi Umum',
                    'description' => 'Bagian ini berisi pertanyaan tentang informasi umum responden',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'type' => 'short-text',
                            'text' => 'Nama Lengkap',
                            'helpText' => 'Masukkan nama lengkap Anda sesuai ijazah',
                            'required' => true,
                            'placeholder' => 'Masukkan nama lengkap Anda'
                        ],
                        [
                            'id' => 'q2',
                            'type' => 'radio',
                            'text' => 'Jenis Kelamin',
                            'helpText' => '',
                            'required' => true,
                            'options' => [
                                ['id' => 'opt1', 'text' => 'Laki-laki', 'value' => 'male'],
                                ['id' => 'opt2', 'text' => 'Perempuan', 'value' => 'female']
                            ]
                        ]
                    ]
                ]
            ],
            'welcomeScreen' => [
                'title' => 'Selamat Datang di Kuesioner Demo',
                'description' => 'Terima kasih telah berpartisipasi dalam tracer study kami.'
            ],
            'thankYouScreen' => [
                'title' => 'Terima Kasih',
                'description' => 'Terima kasih atas partisipasi Anda dalam tracer study ini.'
            ]
        ];
        
        return view('questionnaire.edit', compact('questionnaire'));
    })->name('questionnaires.edit');
    
    // Preview kuesioner (untuk admin)
    Route::get('/{questionnaire}/preview', function($questionnaire) {
        // Gunakan data dummy yang sama dengan edit
        $questionnaire = [
            'id' => $questionnaire,
            'title' => 'Kuesioner Demo',
            'description' => 'Ini adalah kuesioner contoh untuk pengembangan frontend',
            'slug' => 'kuesioner-demo',
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addMonths(3)->format('Y-m-d'),
            'showProgressBar' => true,
            'showPageNumbers' => true,
            'requiresLogin' => false,
            'sections' => [
                [
                    'id' => 'section1',
                    'title' => 'Informasi Umum',
                    'description' => 'Bagian ini berisi pertanyaan tentang informasi umum responden',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'type' => 'short-text',
                            'text' => 'Nama Lengkap',
                            'helpText' => 'Masukkan nama lengkap Anda sesuai ijazah',
                            'required' => true,
                            'placeholder' => 'Masukkan nama lengkap Anda'
                        ],
                        [
                            'id' => 'q2',
                            'type' => 'radio',
                            'text' => 'Jenis Kelamin',
                            'helpText' => '',
                            'required' => true,
                            'options' => [
                                ['id' => 'opt1', 'text' => 'Laki-laki', 'value' => 'male'],
                                ['id' => 'opt2', 'text' => 'Perempuan', 'value' => 'female']
                            ]
                        ]
                    ]
                ]
            ],
            'welcomeScreen' => [
                'title' => 'Selamat Datang di Kuesioner Demo',
                'description' => 'Terima kasih telah berpartisipasi dalam tracer study kami.'
            ],
            'thankYouScreen' => [
                'title' => 'Terima Kasih',
                'description' => 'Terima kasih atas partisipasi Anda dalam tracer study ini.'
            ]
        ];
        
        return view('questionnaire.preview', compact('questionnaire'));
    })->name('questionnaires.preview');
    
    // Untuk routes menyimpan, memperbarui, dan menghapus kuesioner
    // Buat route dummy yang return redirect atau response JSON untuk kebutuhan frontend testing
    Route::post('/', function() {
        // Response JSON untuk simulasi
        return response()->json(['success' => true, 'id' => rand(1, 100)]);
    })->name('questionnaires.store');
    
    Route::put('/{questionnaire}', function() {
        return response()->json(['success' => true]);
    })->name('questionnaires.update');
    
    Route::delete('/{questionnaire}', function() {
        return redirect()->route('questionnaires.index')->with('success', 'Kuesioner berhasil dihapus');
    })->name('questionnaires.destroy');
    
    // Tambahkan route untuk results (dummy)
    Route::get('/{questionnaire}/results', function() {
        return view('questionnaire.results', ['questionnaire' => ['title' => 'Kuesioner Demo']]);
    })->name('questionnaires.results');
});

// Endpoint untuk alumni (tidak perlu login)
Route::prefix('kuesioner')->group(function () {
    // Mengisi kuesioner
    Route::get('/{slug}', function($slug) {
        $questionnaire = [
            'id' => 'demo-' . $slug,
            'title' => 'Kuesioner ' . ucfirst($slug),
            'description' => 'Ini adalah kuesioner untuk diisi oleh alumni',
            'slug' => $slug,
            'requires_login' => false,
            'showProgressBar' => true,
            'showPageNumbers' => true,
            'sections' => [
                [
                    'id' => 'section1',
                    'title' => 'Informasi Umum',
                    'description' => 'Bagian ini berisi pertanyaan tentang informasi umum responden',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'type' => 'short-text',
                            'text' => 'Nama Lengkap',
                            'helpText' => 'Masukkan nama lengkap Anda sesuai ijazah',
                            'required' => true,
                            'placeholder' => 'Masukkan nama lengkap Anda'
                        ],
                        [
                            'id' => 'q2',
                            'type' => 'radio',
                            'text' => 'Jenis Kelamin',
                            'helpText' => '',
                            'required' => true,
                            'options' => [
                                ['id' => 'opt1', 'text' => 'Laki-laki', 'value' => 'male'],
                                ['id' => 'opt2', 'text' => 'Perempuan', 'value' => 'female']
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'section2',
                    'title' => 'Riwayat Pendidikan',
                    'description' => 'Informasi mengenai pendidikan setelah lulus',
                    'questions' => [
                        [
                            'id' => 'q3',
                            'type' => 'long-text',
                            'text' => 'Ceritakan pengalaman pendidikan Anda setelah lulus',
                            'required' => false,
                            'placeholder' => 'Tulis pengalaman Anda di sini'
                        ]
                    ]
                ]
            ],
            'welcomeScreen' => [
                'title' => 'Selamat Datang di Kuesioner Alumni',
                'description' => 'Terima kasih telah berpartisipasi dalam tracer study kami. Data yang Anda berikan sangat berharga untuk pengembangan program studi ke depan.'
            ],
            'thankYouScreen' => [
                'title' => 'Terima Kasih',
                'description' => 'Terima kasih atas partisipasi Anda dalam tracer study ini. Data yang Anda berikan akan digunakan untuk meningkatkan kualitas pembelajaran.'
            ]
        ];
        
        return view('questionnaire.show', compact('questionnaire'));
    })->name('form.show');
    
    // Submit jawaban kuesioner (dummy endpoint)
    Route::post('/submit', function() {
        return response()->json(['success' => true]);
    })->name('form.submit');
});

Route::get('/vue-test', function() {
    return view('vue-test');
})->name('vue-test');