<?php

namespace App\Http\Controllers\Questionnaire;

use App\Http\Controllers\Controller;
use App\Contracts\Services\QuestionnaireServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class PreviewController extends Controller
{
    protected $questionnaireService;

    /**
     * Create a new controller instance.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     * @return void
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
    }

    /**
     * Display the standalone preview page.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $questionnaireId = $request->query('id');
        
        // If an ID is provided, load that specific questionnaire
        if ($questionnaireId) {
            $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
            
            if (!$questionnaire) {
                return view('questionnaire.standalone-preview')->with([
                    'error' => 'Kuesioner tidak ditemukan',
                    'questionnaire' => $this->getSampleQuestionnaire()
                ]);
            }
            
            return view('questionnaire.standalone-preview', compact('questionnaire'));
        }
        
        // Otherwise load a sample questionnaire for demonstration
        return view('questionnaire.standalone-preview')->with([
            'questionnaire' => $this->getSampleQuestionnaire()
        ]);
    }

    /**
     * Get a sample questionnaire data for preview when no ID is provided.
     *
     * @return array
     */
    private function getSampleQuestionnaire(): array
    {
        return [
            'id' => 'sample',
            'title' => 'Kuesioner Demo',
            'description' => 'Ini adalah kuesioner contoh untuk melihat tampilan pratinjau.',
            'status' => 'draft',
            'welcomeScreen' => [
                'title' => 'Selamat Datang di Kuesioner Demo',
                'description' => 'Terima kasih telah berpartisipasi dalam kuesioner ini. Kami menghargai waktu dan masukan Anda.'
            ],
            'thankYouScreen' => [
                'title' => 'Terima Kasih!',
                'description' => 'Respon Anda telah kami catat. Terima kasih atas partisipasi Anda.'
            ],
            'showProgressBar' => true,
            'showPageNumbers' => true,
            'sections' => [
                [
                    'id' => 'section1',
                    'title' => 'Informasi Dasar',
                    'description' => 'Beberapa pertanyaan tentang informasi dasar Anda.',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'text' => 'Siapa nama lengkap Anda?',
                            'type' => 'short-text',
                            'required' => true,
                            'helpText' => 'Masukkan nama lengkap Anda sesuai identitas resmi.'
                        ],
                        [
                            'id' => 'q2',
                            'text' => 'Berapa usia Anda?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => [
                                ['id' => 'opt1', 'text' => '18-24 tahun'],
                                ['id' => 'opt2', 'text' => '25-34 tahun'],
                                ['id' => 'opt3', 'text' => '35-44 tahun'],
                                ['id' => 'opt4', 'text' => '45 tahun ke atas']
                            ]
                        ],
                        [
                            'id' => 'q3',
                            'text' => 'Alamat email Anda',
                            'type' => 'email',
                            'required' => true,
                            'helpText' => 'Kami akan mengirimkan konfirmasi ke alamat email ini.'
                        ]
                    ]
                ],
                [
                    'id' => 'section2',
                    'title' => 'Pendidikan',
                    'description' => 'Pertanyaan tentang latar belakang pendidikan Anda.',
                    'questions' => [
                        [
                            'id' => 'q4',
                            'text' => 'Apa tingkat pendidikan tertinggi Anda?',
                            'type' => 'dropdown',
                            'required' => true,
                            'options' => [
                                ['id' => 'edu1', 'text' => 'SMA/SMK'],
                                ['id' => 'edu2', 'text' => 'Diploma'],
                                ['id' => 'edu3', 'text' => 'Sarjana'],
                                ['id' => 'edu4', 'text' => 'Magister'],
                                ['id' => 'edu5', 'text' => 'Doktor']
                            ]
                        ],
                        [
                            'id' => 'q5',
                            'text' => 'Ceritakan pengalaman belajar Anda selama kuliah',
                            'type' => 'long-text',
                            'required' => false,
                            'helpText' => 'Bagikan pengalaman, tantangan, dan hal menarik selama Anda kuliah.'
                        ]
                    ]
                ],
                [
                    'id' => 'section3',
                    'title' => 'Karir',
                    'description' => 'Pertanyaan tentang karir dan pekerjaan Anda saat ini.',
                    'questions' => [
                        [
                            'id' => 'q6',
                            'text' => 'Apakah Anda sudah bekerja?',
                            'type' => 'yes-no',
                            'required' => true
                        ],
                        [
                            'id' => 'q7',
                            'text' => 'Bidang pekerjaan apa yang Anda tekuni? (pilih semua yang sesuai)',
                            'type' => 'checkbox',
                            'required' => false,
                            'options' => [
                                ['id' => 'field1', 'text' => 'Teknologi Informasi'],
                                ['id' => 'field2', 'text' => 'Pendidikan'],
                                ['id' => 'field3', 'text' => 'Kesehatan'],
                                ['id' => 'field4', 'text' => 'Keuangan'],
                                ['id' => 'field5', 'text' => 'Lainnya']
                            ],
                            'hasOtherOption' => true
                        ],
                        [
                            'id' => 'q8',
                            'text' => 'Seberapa puas Anda dengan pekerjaan saat ini?',
                            'type' => 'rating',
                            'required' => false,
                            'min' => 1,
                            'max' => 5,
                            'labels' => [
                                'min' => 'Sangat Tidak Puas',
                                'max' => 'Sangat Puas'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
} 