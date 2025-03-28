<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TracerStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asumsikan user ID 1 ada (admin/super admin)
        $userId = 1;
        
        // 1. Buat Questionnaire
        $questionnaireId = DB::table('questionnaires')->insertGetId([
            'user_id' => $userId,
            'title' => 'Career Development & Employability 2025',
            'slug' => 'career-development-and-employability-2025',
            'description' => 'Tracer Study Alumni UPNVJ',
            'status' => 'draft',
            'start_date' => null,
            'end_date' => null,
            'is_template' => true,
            'settings' => json_encode([
                'showProgressBar' => true,
                'showPageNumbers' => true,
                'requiresLogin' => false,
                'welcomeScreen' => [
                    'title' => 'Selamat Datang',
                    'description' => 'Terima kasih telah berpartisipasi dalam tracer study kami.'
                ],
                'thankYouScreen' => [
                    'title' => 'Terima Kasih',
                    'description' => 'Terima kasih atas partisipasi Anda dalam tracer study kami.'
                ]
            ]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Array untuk menyimpan data sections dan questions untuk JSON lengkap
        $sectionsData = [];
        
        // 2. Buat Sections
        $sections = [
            [
                'title' => 'Data Diri dan Informasi Kontak',
                'description' => null,
                'order' => 0,
            ],
            [
                'title' => 'Informasi Pekerjaan dan Karir',
                'description' => null,
                'order' => 1,
            ],
            [
                'title' => 'Penilaian Kompetensi dan Kontribusi UPNVJ',
                'description' => null,
                'order' => 2,
            ],
            [
                'title' => 'Evaluasi Layanan dan Kurikulum UPNVJ',
                'description' => null,
                'order' => 3,
            ],
            [
                'title' => 'Masukan dan Saran Pengembangan',
                'description' => null,
                'order' => 4,
            ],
        ];

        foreach ($sections as $index => $section) {
            $sectionId = DB::table('sections')->insertGetId([
                'questionnaire_id' => $questionnaireId,
                'title' => $section['title'],
                'description' => $section['description'],
                'order' => $section['order'],
                'settings' => json_encode([
                    'questionsPerPage' => 3,
                    'showProgressBar' => true,
                    'showPageNumbers' => true
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $sections[$index]['id'] = $sectionId;
            $sections[$index]['questions'] = [];
            
            // Tambahkan section ke array untuk JSON lengkap
            $sectionsData[] = [
                'id' => $sectionId,
                'order' => $section['order'],
                'title' => $section['title'],
                'settings' => null,
                'description' => $section['description'],
                'questionnaire_id' => $questionnaireId,
                'created_at' => Carbon::now()->toISOString(),
                'updated_at' => Carbon::now()->toISOString(),
                'questions' => []
            ];
        }

        // 3. Buat Questions untuk Section 1: Data Diri dan Informasi Kontak
        $section1Questions = [
            [
                'title' => 'Nama Lengkap',
                'description' => 'Tuliskan nama lengkap sesuai ijazah',
                'question_type' => 'text',
                'is_required' => true,
                'order' => 0,
                'settings' => json_encode([
                    'text' => 'Nama Lengkap',
                    'helpText' => 'Tuliskan nama lengkap sesuai ijazah',
                    'required' => true,
                    'type' => 'short-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Nama Lengkap',
                    'description' => 'Tuliskan nama lengkap sesuai ijazah',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'NIM',
                'description' => 'Nomor Induk Mahasiswa saat kuliah di UPNVJ',
                'question_type' => 'text',
                'is_required' => true,
                'order' => 1,
                'settings' => json_encode([
                    'text' => 'NIM',
                    'helpText' => 'Nomor Induk Mahasiswa saat kuliah di UPNVJ',
                    'required' => true,
                    'type' => 'short-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'NIM',
                    'description' => 'Nomor Induk Mahasiswa saat kuliah di UPNVJ',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Email',
                'description' => 'Gunakan email yang aktif untuk komunikasi lanjutan',
                'question_type' => 'text',
                'is_required' => true,
                'order' => 2,
                'settings' => json_encode([
                    'text' => 'Email',
                    'helpText' => 'Gunakan email yang aktif untuk komunikasi lanjutan',
                    'required' => true,
                    'type' => 'email',
                    'placeholder' => 'email@example.com',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Email',
                    'description' => 'Gunakan email yang aktif untuk komunikasi lanjutan',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Nomor Telepon',
                'description' => 'Nomor telepon aktif yang terhubung dengan WhatsApp',
                'question_type' => 'text',
                'is_required' => true,
                'order' => 3,
                'settings' => json_encode([
                    'text' => 'Nomor Telepon',
                    'helpText' => 'Nomor telepon aktif yang terhubung dengan WhatsApp',
                    'required' => true,
                    'type' => 'phone',
                    'placeholder' => '+62',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'format' => 'international',
                    'title' => 'Nomor Telepon',
                    'description' => 'Nomor telepon aktif yang terhubung dengan WhatsApp',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Tanggal Lahir',
                'description' => null,
                'question_type' => 'date',
                'is_required' => true,
                'order' => 4,
                'settings' => json_encode([
                    'text' => 'Tanggal Lahir',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'date',
                    'format' => 'DD/MM/YYYY',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'minDate' => null,
                    'maxDate' => null,
                    'title' => 'Tanggal Lahir',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Jenis Kelamin',
                'description' => null,
                'question_type' => 'radio',
                'is_required' => true,
                'order' => 5,
                'settings' => json_encode([
                    'text' => 'Jenis Kelamin',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'radio',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => false,
                    'title' => 'Jenis Kelamin',
                    'description' => '',
                    'is_required' => true,
                    'allowNone' => false,
                    'optionsOrder' => 'none'
                ]),
                'options' => [
                    ['label' => 'Laki-laki', 'value' => 'Laki-laki', 'order' => 0],
                    ['label' => 'Perempuan', 'value' => 'Perempuan', 'order' => 1]
                ]
            ],
            [
                'title' => 'Fakultas',
                'description' => null,
                'question_type' => 'dropdown',
                'is_required' => true,
                'order' => 6,
                'settings' => json_encode([
                    'text' => 'Fakultas',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'dropdown',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => false,
                    'title' => 'Fakultas',
                    'description' => '',
                    'is_required' => true,
                    'allowNone' => false
                ]),
                'options' => [
                    ['label' => 'Fakultas Ekonomi dan Bisnis', 'value' => 'Fakultas Ekonomi dan Bisnis', 'order' => 0],
                    ['label' => 'Fakultas Ilmu Komputer', 'value' => 'Fakultas Ilmu Komputer', 'order' => 1],
                    ['label' => 'Fakultas Ilmu Sosial dan Politik', 'value' => 'Fakultas Ilmu Sosial dan Politik', 'order' => 2],
                    ['label' => 'Fakultas Kedokteran', 'value' => 'Fakultas Kedokteran', 'order' => 3],
                    ['label' => 'Fakultas Teknik', 'value' => 'Fakultas Teknik', 'order' => 4],
                    ['label' => 'Fakultas Hukum', 'value' => 'Fakultas Hukum', 'order' => 5],
                    ['label' => 'Fakultas Ilmu Kesehatan', 'value' => 'Fakultas Ilmu Kesehatan', 'order' => 6]
                ]
            ],
            [
                'title' => 'Program Studi',
                'description' => 'Tuliskan program studi yang diambil saat kuliah',
                'question_type' => 'text',
                'is_required' => true,
                'order' => 7,
                'settings' => json_encode([
                    'text' => 'Program Studi',
                    'helpText' => 'Tuliskan program studi yang diambil saat kuliah',
                    'required' => true,
                    'type' => 'short-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Program Studi',
                    'description' => 'Tuliskan program studi yang diambil saat kuliah',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Tahun Masuk',
                'description' => null,
                'question_type' => 'date',
                'is_required' => true,
                'order' => 8,
                'settings' => json_encode([
                    'text' => 'Tahun Masuk',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'date',
                    'format' => 'DD/MM/YYYY',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'minDate' => null,
                    'maxDate' => null,
                    'title' => 'Tahun Masuk',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Tahun Lulus',
                'description' => null,
                'question_type' => 'date',
                'is_required' => true,
                'order' => 9,
                'settings' => json_encode([
                    'text' => 'Tahun Lulus',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'date',
                    'format' => 'DD/MM/YYYY',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'minDate' => null,
                    'maxDate' => null,
                    'title' => 'Tahun Lulus',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ]
        ];

        // 4. Buat Questions untuk Section 2: Informasi Pekerjaan dan Karir
        $section2Questions = [
            [
                'title' => 'Status Pekerjaan Saat Ini',
                'description' => null,
                'question_type' => 'radio',
                'is_required' => true,
                'order' => 0,
                'settings' => json_encode([
                    'text' => 'Status Pekerjaan Saat Ini',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'radio',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => true,
                    'title' => 'Status Pekerjaan Saat Ini',
                    'description' => '',
                    'is_required' => true,
                    'allowNone' => false,
                    'optionsOrder' => 'none'
                ]),
                'options' => [
                    ['label' => 'Bekerja (Full time)', 'value' => 'Bekerja (Full time)', 'order' => 0],
                    ['label' => 'Bekerja (Part time)', 'value' => 'Bekerja (Part time)', 'order' => 1],
                    ['label' => 'Wiraswasta/Entrepreneur', 'value' => 'Wiraswasta/Entrepreneur', 'order' => 2],
                    ['label' => 'Melanjutkan Studi', 'value' => 'Melanjutkan Studi', 'order' => 3],
                    ['label' => 'Belum Bekerja', 'value' => 'Belum Bekerja', 'order' => 4],
                    ['label' => 'Lainnya', 'value' => 'Lainnya', 'order' => 5]
                ]
            ],
            [
                'title' => 'Jika Anda belum bekerja, apa alasannya?',
                'description' => null,
                'question_type' => 'checkbox',
                'is_required' => false,
                'order' => 1,
                'settings' => json_encode([
                    'text' => 'Jika Anda belum bekerja, apa alasannya?',
                    'helpText' => '',
                    'required' => false,
                    'type' => 'checkbox',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => true,
                    'title' => 'Jika Anda belum bekerja, apa alasannya?',
                    'description' => '',
                    'is_required' => false,
                    'allowNone' => false,
                    'allowSelectAll' => false,
                    'optionsOrder' => 'none',
                    'minSelected' => 0,
                    'maxSelected' => 0
                ]),
                'options' => [
                    ['label' => 'Sedang mencari pekerjaan', 'value' => 'Sedang mencari pekerjaan', 'order' => 0],
                    ['label' => 'Sedang mempersiapkan usaha sendiri', 'value' => 'Sedang mempersiapkan usaha sendiri', 'order' => 1],
                    ['label' => 'Sedang mempersiapkan studi lanjut', 'value' => 'Sedang mempersiapkan studi lanjut', 'order' => 2],
                    ['label' => 'Mengurus keluarga', 'value' => 'Mengurus keluarga', 'order' => 3],
                    ['label' => 'Tidak ingin bekerja dulu', 'value' => 'Tidak ingin bekerja dulu', 'order' => 4],
                    ['label' => 'Lainnya', 'value' => 'Lainnya', 'order' => 5]
                ]
            ],
            [
                'title' => 'Berapa lama waktu yang Anda butuhkan untuk mendapatkan pekerjaan pertama setelah lulus?',
                'description' => null,
                'question_type' => 'radio',
                'is_required' => true,
                'order' => 2,
                'settings' => json_encode([
                    'text' => 'Berapa lama waktu yang Anda butuhkan untuk mendapatkan pekerjaan pertama setelah lulus?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'radio',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => false,
                    'title' => 'Berapa lama waktu yang Anda butuhkan untuk mendapatkan pekerjaan pertama setelah lulus?',
                    'description' => '',
                    'is_required' => true,
                    'allowNone' => false,
                    'optionsOrder' => 'none'
                ]),
                'options' => [
                    ['label' => 'Sudah bekerja sebelum lulus', 'value' => 'Sudah bekerja sebelum lulus', 'order' => 0],
                    ['label' => '< 3 bulan setelah lulus', 'value' => '< 3 bulan setelah lulus', 'order' => 1],
                    ['label' => '3-6 bulan setelah lulus', 'value' => '3-6 bulan setelah lulus', 'order' => 2],
                    ['label' => '6-12 bulan setelah lulus', 'value' => '6-12 bulan setelah lulus', 'order' => 3],
                    ['label' => '> 12 bulan setelah lulus', 'value' => '> 12 bulan setelah lulus', 'order' => 4],
                    ['label' => 'Belum pernah bekerja', 'value' => 'Belum pernah bekerja', 'order' => 5]
                ]
            ],
            [
                'title' => 'Nama Perusahaan/Instansi Tempat Bekerja',
                'description' => 'Tuliskan nama lengkap perusahaan/instansi tempat Anda bekerja saat ini',
                'question_type' => 'text',
                'is_required' => false,
                'order' => 3,
                'settings' => json_encode([
                    'text' => 'Nama Perusahaan/Instansi Tempat Bekerja',
                    'helpText' => 'Tuliskan nama lengkap perusahaan/instansi tempat Anda bekerja saat ini',
                    'required' => false,
                    'type' => 'short-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Nama Perusahaan/Instansi Tempat Bekerja',
                    'description' => 'Tuliskan nama lengkap perusahaan/instansi tempat Anda bekerja saat ini',
                    'is_required' => false
                ]),
                'options' => []
            ],
            [
                'title' => 'Jabatan/Posisi',
                'description' => 'Posisi atau jabatan Anda saat ini',
                'question_type' => 'text',
                'is_required' => false,
                'order' => 4,
                'settings' => json_encode([
                    'text' => 'Jabatan/Posisi',
                    'helpText' => 'Posisi atau jabatan Anda saat ini',
                    'required' => false,
                    'type' => 'short-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Jabatan/Posisi',
                    'description' => 'Posisi atau jabatan Anda saat ini',
                    'is_required' => false
                ]),
                'options' => []
            ],
            [
                'title' => 'Bidang Pekerjaan',
                'description' => null,
                'question_type' => 'dropdown',
                'is_required' => true,
                'order' => 5,
                'settings' => json_encode([
                    'text' => 'Bidang Pekerjaan',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'dropdown',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => true,
                    'title' => 'Bidang Pekerjaan',
                    'description' => '',
                    'is_required' => true,
                    'allowNone' => false
                ]),
                'options' => [
                    ['label' => 'Pendidikan', 'value' => 'Pendidikan', 'order' => 0],
                    ['label' => 'Kesehatan', 'value' => 'Kesehatan', 'order' => 1],
                    ['label' => 'Teknologi Informasi', 'value' => 'Teknologi Informasi', 'order' => 2],
                    ['label' => 'Keuangan dan Perbankan', 'value' => 'Keuangan dan Perbankan', 'order' => 3],
                    ['label' => 'Perdagangan dan Jasa', 'value' => 'Perdagangan dan Jasa', 'order' => 4],
                    ['label' => 'Manufaktur', 'value' => 'Manufaktur', 'order' => 5],
                    ['label' => 'Pertanian dan Peternakan', 'value' => 'Pertanian dan Peternakan', 'order' => 6],
                    ['label' => 'Pertambangan dan Energi', 'value' => 'Pertambangan dan Energi', 'order' => 7],
                    ['label' => 'Konstruksi dan Properti', 'value' => 'Konstruksi dan Properti', 'order' => 8],
                    ['label' => 'Media dan Komunikasi', 'value' => 'Media dan Komunikasi', 'order' => 9],
                    ['label' => 'Transportasi dan Logistik', 'value' => 'Transportasi dan Logistik', 'order' => 10],
                    ['label' => 'Pariwisata dan Perhotelan', 'value' => 'Pariwisata dan Perhotelan', 'order' => 11],
                    ['label' => 'Pemerintahan', 'value' => 'Pemerintahan', 'order' => 12],
                    ['label' => 'BUMN', 'value' => 'BUMN', 'order' => 13],
                    ['label' => 'Lainnya', 'value' => 'Lainnya', 'order' => 14]
                ]
            ],
            [
                'title' => 'Kisaran Gaji/Pendapatan Bulanan',
                'description' => null,
                'question_type' => 'radio',
                'is_required' => true,
                'order' => 6,
                'settings' => json_encode([
                    'text' => 'Kisaran Gaji/Pendapatan Bulanan',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'radio',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => false,
                    'title' => 'Kisaran Gaji/Pendapatan Bulanan',
                    'description' => '',
                    'is_required' => true,
                    'allowNone' => false,
                    'optionsOrder' => 'none'
                ]),
                'options' => [
                    ['label' => '< Rp 3.000.000', 'value' => '< Rp 3.000.000', 'order' => 0],
                    ['label' => 'Rp 3.000.000 - Rp 5.000.000', 'value' => 'Rp 3.000.000 - Rp 5.000.000', 'order' => 1],
                    ['label' => 'Rp 5.000.000 - Rp 8.000.000', 'value' => 'Rp 5.000.000 - Rp 8.000.000', 'order' => 2],
                    ['label' => 'Rp 8.000.000 - Rp 12.000.000', 'value' => 'Rp 8.000.000 - Rp 12.000.000', 'order' => 3],
                    ['label' => 'Rp 12.000.000 - Rp 20.000.000', 'value' => 'Rp 12.000.000 - Rp 20.000.000', 'order' => 4],
                    ['label' => '> Rp 20.000.000', 'value' => '> Rp 20.000.000', 'order' => 5],
                    ['label' => 'Tidak ingin menyebutkan', 'value' => 'Tidak ingin menyebutkan', 'order' => 6]
                ]
            ],
            [
                'title' => 'Bagaimana tingkat kesesuaian pekerjaan Anda saat ini dengan latar belakang pendidikan Anda?',
                'description' => null,
                'question_type' => 'likert',
                'is_required' => true,
                'order' => 7,
                'settings' => json_encode([
                    'text' => 'Bagaimana tingkat kesesuaian pekerjaan Anda saat ini dengan latar belakang pendidikan Anda?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'likert',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'statements' => [
                        [
                            'id' => Str::uuid()->toString(), 
                            'text' => 'Bagaimana tingkat kesesuaian pekerjaan Anda saat ini dengan latar belakang pendidikan Anda?'
                        ]
                    ],
                    'scale' => [
                        ['value' => 1, 'label' => 'Sangat Tidak Sesuai'],
                        ['value' => 2, 'label' => 'Tidak Setuju'],
                        ['value' => 3, 'label' => 'Netral'],
                        ['value' => 4, 'label' => 'Setuju'],
                        ['value' => 5, 'label' => 'Sangat Sesuai']
                    ],
                    'title' => 'Bagaimana tingkat kesesuaian pekerjaan Anda saat ini dengan latar belakang pendidikan Anda?',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Berapa tingkat kepuasan Anda terhadap pekerjaan saat ini?',
                'description' => null,
                'question_type' => 'rating',
                'is_required' => true,
                'order' => 8,
                'settings' => json_encode([
                    'text' => 'Berapa tingkat kepuasan Anda terhadap pekerjaan saat ini?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'rating',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'maxRating' => 5,
                    'labels' => [
                        '1' => 'Sangat Tidak Puas',
                        '5' => 'Sangat Puas'
                    ],
                    'title' => 'Berapa tingkat kepuasan Anda terhadap pekerjaan saat ini?',
                    'description' => '',
                    'is_required' => true,
                    'minRating' => 1,
                    'maxRatingValue' => 5,
                    'stepValue' => 1
                ]),
                'options' => []
            ]
        ];

        // 5. Buat Questions untuk Section 3: Penilaian Kompetensi dan Kontribusi UPNVJ
        $section3Questions = [
            [
                'title' => 'Berikan penilaian terhadap kontribusi pendidikan di UPNVJ terhadap kompetensi berikut',
                'description' => null,
                'question_type' => 'matrix',
                'is_required' => true,
                'order' => 0,
                'settings' => json_encode([
                    'text' => 'Berikan penilaian terhadap kontribusi pendidikan di UPNVJ terhadap kompetensi berikut',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'matrix',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'rows' => [
                        ['id' => Str::uuid()->toString(), 'text' => 'Pengetahuan bidang ilmu'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Keterampilan praktis'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Kemampuan komunikasi'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Kemampuan bekerja sama dalam tim'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Kemampuan berbahasa asing'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Penggunaan teknologi informasi'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Keterampilan kepemimpinan'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Etika profesional'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Kemampuan beradaptasi'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Inovasi dan kreativitas']
                    ],
                    'columns' => [
                        ['id' => Str::uuid()->toString(), 'text' => 'Sangat Rendah'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Rendah'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Cukup'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Tinggi'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Sangat Tinggi']
                    ],
                    'matrixType' => 'radio',
                    'title' => 'Berikan penilaian terhadap kontribusi pendidikan di UPNVJ terhadap kompetensi berikut',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Urutkan faktor berikut berdasarkan kontribusinya terhadap kesuksesan karir Anda',
                'description' => null,
                'question_type' => 'ranking',
                'is_required' => true,
                'order' => 1,
                'settings' => json_encode([
                    'text' => 'Urutkan faktor berikut berdasarkan kontribusinya terhadap kesuksesan karir Anda',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'ranking',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Urutkan faktor berikut berdasarkan kontribusinya terhadap kesuksesan karir Anda',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => [
                    ['label' => 'Pengetahuan Akademik', 'value' => 'Pengetahuan Akademik', 'order' => 0],
                    ['label' => 'Jaringan/koneksi', 'value' => 'Jaringan/koneksi', 'order' => 1],
                    ['label' => 'Pengalaman magang/kerja selama kuliah', 'value' => 'Pengalaman magang/kerja selama kuliah', 'order' => 2],
                    ['label' => 'Aktivitas organisasi', 'value' => 'Aktivitas organisasi', 'order' => 3],
                    ['label' => 'Sertifikasi tambahan', 'value' => 'Sertifikasi tambahan', 'order' => 4],
                    ['label' => 'Kemampuan berbahasa asing', 'value' => 'Kemampuan berbahasa asing', 'order' => 5]
                ]
            ],
            [
                'title' => 'Apakah Anda pernah mengikuti pelatihan/sertifikasi tambahan untuk menunjang karir?',
                'description' => null,
                'question_type' => 'yes-no',
                'is_required' => true,
                'order' => 2,
                'settings' => json_encode([
                    'text' => 'Apakah Anda pernah mengikuti pelatihan/sertifikasi tambahan untuk menunjang karir?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'yes-no',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'yesLabel' => 'Ya',
                    'noLabel' => 'Tidak',
                    'title' => 'Apakah Anda pernah mengikuti pelatihan/sertifikasi tambahan untuk menunjang karir?',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Jika ya, sebutkan pelatihan/sertifikasi yang pernah Anda ikuti',
                'description' => 'Tuliskan nama pelatihan/sertifikasi, penyelenggara, dan tahun',
                'question_type' => 'textarea',
                'is_required' => false,
                'order' => 3,
                'settings' => json_encode([
                    'text' => 'Jika ya, sebutkan pelatihan/sertifikasi yang pernah Anda ikuti',
                    'helpText' => 'Tuliskan nama pelatihan/sertifikasi, penyelenggara, dan tahun',
                    'required' => false,
                    'type' => 'long-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Jika ya, sebutkan pelatihan/sertifikasi yang pernah Anda ikuti',
                    'description' => 'Tuliskan nama pelatihan/sertifikasi, penyelenggara, dan tahun',
                    'is_required' => false
                ]),
                'options' => []
            ]
        ];

        // 6. Buat Questions untuk Section 4: Evaluasi Layanan dan Kurikulum UPNVJ
        $section4Questions = [
            [
                'title' => 'Berikan penilaian terhadap aspek-aspek berikut di UPNVJ',
                'description' => null,
                'question_type' => 'matrix',
                'is_required' => true,
                'order' => 0,
                'settings' => json_encode([
                    'text' => 'Berikan penilaian terhadap aspek-aspek berikut di UPNVJ',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'matrix',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'rows' => [
                        ['id' => Str::uuid()->toString(), 'text' => 'Kualitas dosen'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Kurikulum pembelajaran'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Fasilitas perkuliahan'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Layanan akademik'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Layanan kemahasiswaan'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Bimbingan karir'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Kesempatan magang'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Jaringan alumni'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Perpustakaan'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Laboratorium']
                    ],
                    'columns' => [
                        ['id' => Str::uuid()->toString(), 'text' => 'Sangat Buruk'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Buruk'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Cukup'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Baik'],
                        ['id' => Str::uuid()->toString(), 'text' => 'Sangat Baik']
                    ],
                    'matrixType' => 'radio',
                    'title' => 'Berikan penilaian terhadap aspek-aspek berikut di UPNVJ',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Menurut Anda, aspek apa dalam kurikulum yang perlu ditingkatkan?',
                'description' => null,
                'question_type' => 'checkbox',
                'is_required' => true,
                'order' => 1,
                'settings' => json_encode([
                    'text' => 'Menurut Anda, aspek apa dalam kurikulum yang perlu ditingkatkan?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'checkbox',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'allowOther' => true,
                    'allowNone' => false,
                    'allowSelectAll' => false,
                    'optionsOrder' => 'none',
                    'minSelected' => 0,
                    'maxSelected' => 0,
                    'title' => 'Menurut Anda, aspek apa dalam kurikulum yang perlu ditingkatkan?',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => [
                    ['label' => 'Praktikum/praktik lapangan', 'value' => 'Praktikum/praktik lapangan', 'order' => 0],
                    ['label' => 'Materi pembelajaran yang lebih update', 'value' => 'Materi pembelajaran yang lebih update', 'order' => 1],
                    ['label' => 'Keseimbangan teori dan praktik', 'value' => 'Keseimbangan teori dan praktik', 'order' => 2],
                    ['label' => 'Keterampilan bahasa asing', 'value' => 'Keterampilan bahasa asing', 'order' => 3],
                    ['label' => 'Keterampilan komunikasi', 'value' => 'Keterampilan komunikasi', 'order' => 4],
                    ['label' => 'Keterampilan teknologi informasi', 'value' => 'Keterampilan teknologi informasi', 'order' => 5],
                    ['label' => 'Keterampilan kepemimpinan', 'value' => 'Keterampilan kepemimpinan', 'order' => 6],
                    ['label' => 'Kerjasama dengan industri', 'value' => 'Kerjasama dengan industri', 'order' => 7],
                    ['label' => 'Penelitian', 'value' => 'Penelitian', 'order' => 8],
                    ['label' => 'Lainnya', 'value' => 'Lainnya', 'order' => 9]
                ]
            ],
            [
                'title' => 'Seberapa mungkin Anda merekomendasikan UPNVJ kepada orang lain?',
                'description' => '(0: Sangat tidak mungkin, 100: Sangat mungkin)',
                'question_type' => 'slider',
                'is_required' => false,
                'order' => 2,
                'settings' => json_encode([
                    'text' => 'Seberapa mungkin Anda merekomendasikan UPNVJ kepada orang lain?',
                    'helpText' => '(0: Sangat tidak mungkin, 100: Sangat mungkin)',
                    'required' => false,
                    'type' => 'slider',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                    'showTicks' => true,
                    'showLabels' => true,
                    'labels' => [
                        '0' => 'Minimum',
                        '100' => 'Maximum'
                    ],
                    'title' => 'Seberapa mungkin Anda merekomendasikan UPNVJ kepada orang lain?',
                    'description' => '(0: Sangat tidak mungkin, 100: Sangat mungkin)',
                    'is_required' => false
                ]),
                'options' => []
            ],
            [
                'title' => 'Apakah Anda bersedia menjadi narasumber/pembicara dalam kegiatan kampus?',
                'description' => null,
                'question_type' => 'yes-no',
                'is_required' => true,
                'order' => 3,
                'settings' => json_encode([
                    'text' => 'Apakah Anda bersedia menjadi narasumber/pembicara dalam kegiatan kampus?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'yes-no',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'yesLabel' => 'Ya',
                    'noLabel' => 'Tidak',
                    'title' => 'Apakah Anda bersedia menjadi narasumber/pembicara dalam kegiatan kampus?',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ]
        ];

        // 7. Buat Questions untuk Section 5: Masukan dan Saran Pengembangan
        $section5Questions = [
            [
                'title' => 'Berdasarkan pengalaman Anda di dunia kerja, kompetensi apa yang seharusnya lebih ditekankan dalam kurikulum UPNVJ?',
                'description' => 'Berikan masukan berdasarkan kebutuhan dunia kerja saat ini',
                'question_type' => 'textarea',
                'is_required' => true,
                'order' => 0,
                'settings' => json_encode([
                    'text' => 'Berdasarkan pengalaman Anda di dunia kerja, kompetensi apa yang seharusnya lebih ditekankan dalam kurikulum UPNVJ?',
                    'helpText' => 'Berikan masukan berdasarkan kebutuhan dunia kerja saat ini',
                    'required' => true,
                    'type' => 'long-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Berdasarkan pengalaman Anda di dunia kerja, kompetensi apa yang seharusnya lebih ditekankan dalam kurikulum UPNVJ?',
                    'description' => 'Berikan masukan berdasarkan kebutuhan dunia kerja saat ini',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Berikan saran untuk pengembangan program studi Anda di UPNVJ',
                'description' => 'Saran dapat mencakup kurikulum, metode pembelajaran, fasilitas, dll.',
                'question_type' => 'textarea',
                'is_required' => true,
                'order' => 1,
                'settings' => json_encode([
                    'text' => 'Berikan saran untuk pengembangan program studi Anda di UPNVJ',
                    'helpText' => 'Saran dapat mencakup kurikulum, metode pembelajaran, fasilitas, dll.',
                    'required' => true,
                    'type' => 'long-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Berikan saran untuk pengembangan program studi Anda di UPNVJ',
                    'description' => 'Saran dapat mencakup kurikulum, metode pembelajaran, fasilitas, dll.',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Menurut Anda, bagaimana UPNVJ dapat meningkatkan layanan kepada alumni?',
                'description' => 'Berikan saran terkait program, jaringan, atau kegiatan untuk alumni',
                'question_type' => 'textarea',
                'is_required' => true,
                'order' => 2,
                'settings' => json_encode([
                    'text' => 'Menurut Anda, bagaimana UPNVJ dapat meningkatkan layanan kepada alumni?',
                    'helpText' => 'Berikan saran terkait program, jaringan, atau kegiatan untuk alumni',
                    'required' => true,
                    'type' => 'long-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Menurut Anda, bagaimana UPNVJ dapat meningkatkan layanan kepada alumni?',
                    'description' => 'Berikan saran terkait program, jaringan, atau kegiatan untuk alumni',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Sebutkan kendala utama yang Anda hadapi ketika memasuki dunia kerja',
                'description' => 'Kendala dapat terkait kompetensi, kesiapan mental, persaingan, dll.',
                'question_type' => 'textarea',
                'is_required' => false,
                'order' => 3,
                'settings' => json_encode([
                    'text' => 'Sebutkan kendala utama yang Anda hadapi ketika memasuki dunia kerja',
                    'helpText' => 'Kendala dapat terkait kompetensi, kesiapan mental, persaingan, dll.',
                    'required' => false,
                    'type' => 'long-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Sebutkan kendala utama yang Anda hadapi ketika memasuki dunia kerja',
                    'description' => 'Kendala dapat terkait kompetensi, kesiapan mental, persaingan, dll.',
                    'is_required' => false
                ]),
                'options' => []
            ],
            [
                'title' => 'Apakah Anda pernah berpartisipasi dalam kegiatan alumni UPNVJ?',
                'description' => null,
                'question_type' => 'yes-no',
                'is_required' => true,
                'order' => 4,
                'settings' => json_encode([
                    'text' => 'Apakah Anda pernah berpartisipasi dalam kegiatan alumni UPNVJ?',
                    'helpText' => '',
                    'required' => true,
                    'type' => 'yes-no',
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'yesLabel' => 'Ya',
                    'noLabel' => 'Tidak',
                    'title' => 'Apakah Anda pernah berpartisipasi dalam kegiatan alumni UPNVJ?',
                    'description' => '',
                    'is_required' => true
                ]),
                'options' => []
            ],
            [
                'title' => 'Jika ada, kegiatan/program apa yang ingin Anda usulkan untuk memperkuat jaringan alumni UPNVJ?',
                'description' => 'Usulkan ide kegiatan yang dapat memperkuat kolaborasi antar alumni dan dengan kampus',
                'question_type' => 'textarea',
                'is_required' => true,
                'order' => 5,
                'settings' => json_encode([
                    'text' => 'Jika ada, kegiatan/program apa yang ingin Anda usulkan untuk memperkuat jaringan alumni UPNVJ?',
                    'helpText' => 'Usulkan ide kegiatan yang dapat memperkuat kolaborasi antar alumni dan dengan kampus',
                    'required' => true,
                    'type' => 'long-text',
                    'placeholder' => '',
                    'maxLength' => 0,
                    'id' => Str::uuid()->toString(),
                    'visible' => true,
                    'title' => 'Jika ada, kegiatan/program apa yang ingin Anda usulkan untuk memperkuat jaringan alumni UPNVJ?',
                    'description' => 'Usulkan ide kegiatan yang dapat memperkuat kolaborasi antar alumni dan dengan kampus',
                    'is_required' => true
                ]),
                'options' => []
            ]
        ];

        // 8. Gabungkan semua questions
        $allSectionQuestions = [
            0 => $section1Questions,
            1 => $section2Questions,
            2 => $section3Questions,
            3 => $section4Questions,
            4 => $section5Questions
        ];

        // 9. Proses untuk menyimpan questions dan options ke database
        foreach ($allSectionQuestions as $sectionIndex => $sectionQuestions) {
            $sectionId = $sections[$sectionIndex]['id'];
            
            foreach ($sectionQuestions as $questionIndex => $question) {
                $questionId = DB::table('questions')->insertGetId([
                    'section_id' => $sectionId,
                    'questionnaire_id' => $questionnaireId,
                    'question_type' => $question['question_type'],
                    'title' => $question['title'],
                    'description' => $question['description'],
                    'is_required' => $question['is_required'],
                    'order' => $question['order'],
                    'settings' => $question['settings'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Tambahkan question ke array untuk JSON lengkap
                $questionData = [
                    'id' => $questionId,
                    'order' => $question['order'],
                    'title' => $question['title'],
                    'options' => [],
                    'settings' => $question['settings'],
                    'created_at' => Carbon::now()->toISOString(),
                    'section_id' => $sectionId,
                    'updated_at' => Carbon::now()->toISOString(),
                    'description' => $question['description'],
                    'is_required' => $question['is_required'],
                    'question_type' => $question['question_type'],
                    'questionnaire_id' => $questionnaireId
                ];

                // Tambahkan options jika ada
                if (!empty($question['options'])) {
                    foreach ($question['options'] as $optionIndex => $option) {
                        $optionId = DB::table('options')->insertGetId([
                            'question_id' => $questionId,
                            'value' => $option['value'],
                            'label' => $option['label'],
                            'order' => $option['order'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                        // Tambahkan option ke array untuk JSON lengkap
                        $questionData['options'][] = [
                            'id' => $optionId,
                            'label' => $option['label'],
                            'order' => $option['order'],
                            'value' => $option['value'],
                            'created_at' => Carbon::now()->toISOString(),
                            'updated_at' => Carbon::now()->toISOString(),
                            'question_id' => $questionId
                        ];
                    }
                }

                // Tambahkan question ke section dalam array untuk JSON lengkap
                $sectionsData[$sectionIndex]['questions'][] = $questionData;
            }
        }

        // 10. Update questionnaire dengan JSON lengkap
        $questionnaireJson = [
            'id' => $questionnaireId,
            'slug' => 'career-development-and-employability-2025',
            'title' => 'Career Development & Employability 2025',
            'status' => 'draft',
            'end_date' => null,
            'sections' => $sectionsData,
            'settings' => json_encode([
                'showProgressBar' => true,
                'showPageNumbers' => true,
                'requiresLogin' => false,
                'welcomeScreen' => [
                    'title' => 'Selamat Datang',
                    'description' => 'Terima kasih telah berpartisipasi dalam tracer study kami.'
                ],
                'thankYouScreen' => [
                    'title' => 'Terima Kasih',
                    'description' => 'Terima kasih atas partisipasi Anda dalam tracer study kami.'
                ]
            ]),
            'start_date' => null,
            'description' => 'Tracer Study Alumni UPNVJ'
        ];

        // Update questionnaire dengan JSON lengkap
        DB::table('questionnaires')->where('id', $questionnaireId)->update([
            'questionnaire_json' => json_encode($questionnaireJson)
        ]);

        $this->command->info('Tracer Study seeder berhasil dijalankan!');
    }
}