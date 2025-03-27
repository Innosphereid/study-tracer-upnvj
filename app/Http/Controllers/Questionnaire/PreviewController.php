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
            
            // Ensure we have the questionnaire_json data loaded
            if (empty($questionnaire->questionnaire_json)) {
                Log::info('Generating JSON representation for standalone preview', ['id' => $questionnaireId]);
                $questionnaire->storeAsJson();
                $questionnaire->refresh();
            }
            
            // Use the JSON representation for the preview as it's more complete
            $previewData = $questionnaire->questionnaire_json;
            
            // Transform the data to match frontend expectations
            $previewData = $this->transformQuestionnaireData($previewData);
            
            Log::debug('Prepared standalone preview data', [
                'id' => $questionnaireId, 
                'sections_count' => isset($previewData['sections']) ? count($previewData['sections']) : 0
            ]);
            
            return view('questionnaire.standalone-preview', ['questionnaire' => $previewData]);
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

    /**
     * Transform questionnaire data to match frontend component expectations
     *
     * @param array $data
     * @return array
     */
    private function transformQuestionnaireData(array $data): array
    {
        // Add necessary structure expected by the frontend components
        if (!isset($data['welcomeScreen']) && isset($data['settings'])) {
            // Extract welcome screen from settings if available
            if (isset($data['settings']['welcomeScreen'])) {
                $data['welcomeScreen'] = $data['settings']['welcomeScreen'];
            } else {
                // Create default welcome screen
                $data['welcomeScreen'] = [
                    'title' => 'Selamat Datang',
                    'description' => 'Terima kasih telah berpartisipasi dalam kuesioner ini.'
                ];
            }
            
            // Extract thank you screen from settings if available
            if (isset($data['settings']['thankYouScreen'])) {
                $data['thankYouScreen'] = $data['settings']['thankYouScreen'];
            } else {
                // Create default thank you screen
                $data['thankYouScreen'] = [
                    'title' => 'Terima Kasih',
                    'description' => 'Terima kasih atas partisipasi Anda.'
                ];
            }
            
            // Add progress bar setting
            if (!isset($data['showProgressBar']) && isset($data['settings']['showProgressBar'])) {
                $data['showProgressBar'] = $data['settings']['showProgressBar'];
            } else {
                $data['showProgressBar'] = true;
            }
            
            // Add page numbers setting
            if (!isset($data['showPageNumbers']) && isset($data['settings']['showPageNumbers'])) {
                $data['showPageNumbers'] = $data['settings']['showPageNumbers'];
            } else {
                $data['showPageNumbers'] = true;
            }
        }
        
        // Process sections to ensure each question has the expected structure
        if (isset($data['sections']) && is_array($data['sections'])) {
            foreach ($data['sections'] as &$section) {
                // Process section settings
                if (!isset($section['settings']) && isset($section['settings_json'])) {
                    $section['settings'] = json_decode($section['settings_json'], true);
                }
                
                if (isset($section['questions']) && is_array($section['questions'])) {
                    foreach ($section['questions'] as &$question) {
                        // Ensure each question has a type field matching frontend expectations
                        if (!isset($question['type']) && isset($question['question_type'])) {
                            // Map backend types to frontend types
                            $typeMap = [
                                'text' => 'short-text',
                                'textarea' => 'long-text',
                                'radio' => 'radio',
                                'checkbox' => 'checkbox',
                                'dropdown' => 'dropdown',
                                'rating' => 'rating',
                                'date' => 'date',
                                'file' => 'file-upload',
                                'matrix' => 'matrix'
                            ];
                            
                            $question['type'] = $typeMap[$question['question_type']] ?? 'short-text';
                        }
                        
                        // Map title to text for frontend components
                        if (!isset($question['text']) && isset($question['title'])) {
                            $question['text'] = $question['title'];
                        }
                        
                        // Map description to helpText for frontend components
                        if (!isset($question['helpText']) && isset($question['description'])) {
                            $question['helpText'] = $question['description'];
                        }
                        
                        // Map is_required to required for frontend components
                        if (!isset($question['required']) && isset($question['is_required'])) {
                            $question['required'] = (bool)$question['is_required'];
                        }
                        
                        // Process question settings
                        if (isset($question['settings'])) {
                            // If settings is a string, decode it
                            if (is_string($question['settings'])) {
                                $questionSettings = json_decode($question['settings'], true);
                                $question['settings'] = $questionSettings;
                                Log::debug('Decoded question settings', [
                                    'question_id' => $question['id'] ?? 'unknown',
                                    'has_settings' => !empty($questionSettings),
                                    'settings_keys' => $questionSettings ? array_keys($questionSettings) : []
                                ]);
                                
                                // Handle nested settings
                                if (isset($questionSettings['settings'])) {
                                    // If we find a settings property within settings, it's a nested structure
                                    Log::debug('Found nested settings, cleaning up', [
                                        'question_id' => $question['id'] ?? 'unknown'
                                    ]);
                                    
                                    if (is_string($questionSettings['settings'])) {
                                        // Try to decode another level
                                        $nestedSettings = json_decode($questionSettings['settings'], true);
                                        if (is_array($nestedSettings)) {
                                            // Merge with outer settings at the same level
                                            foreach ($nestedSettings as $key => $value) {
                                                if (!isset($questionSettings[$key])) {
                                                    $questionSettings[$key] = $value;
                                                }
                                            }
                                        }
                                    } elseif (is_array($questionSettings['settings'])) {
                                        // Merge array settings
                                        foreach ($questionSettings['settings'] as $key => $value) {
                                            if (!isset($questionSettings[$key])) {
                                                $questionSettings[$key] = $value;
                                            }
                                        }
                                    }
                                    
                                    // Remove the nested settings to avoid duplication
                                    unset($questionSettings['settings']);
                                    $question['settings'] = $questionSettings;
                                    
                                    Log::debug('Cleaned settings structure', [
                                        'question_id' => $question['id'] ?? 'unknown',
                                        'clean_settings_keys' => array_keys($questionSettings)
                                    ]);
                                }
                            }
                            
                            // Use settings values if frontend properties are not set
                            if (isset($question['settings']['required']) && !isset($question['required'])) {
                                $question['required'] = (bool)$question['settings']['required'];
                            }
                            
                            if (isset($question['settings']['text']) && !isset($question['text'])) {
                                $question['text'] = $question['settings']['text'];
                            }
                            
                            if (isset($question['settings']['helpText']) && !isset($question['helpText'])) {
                                $question['helpText'] = $question['settings']['helpText'];
                            }
                            
                            // Handle question type-specific settings
                            if (isset($question['type']) && isset($question['settings']) && is_array($question['settings'])) {
                                // Copy all settings to the question root for the frontend
                                foreach ($question['settings'] as $key => $value) {
                                    // Avoid overriding existing properties and special properties
                                    $specialKeys = ['text', 'helpText', 'required', 'id', 'question_id', 'section_id', 'order', 'created_at', 'updated_at', 'settings'];
                                    if (!isset($question[$key]) && !in_array($key, $specialKeys)) {
                                        $question[$key] = $value;
                                    }
                                }
                                
                                // Type-specific handling
                                switch ($question['type']) {
                                    case 'radio':
                                    case 'checkbox':
                                    case 'dropdown':
                                        // Ensure options are available
                                        if (!isset($question['options']) && isset($question['settings']['options'])) {
                                            $question['options'] = $question['settings']['options'];
                                        }
                                        
                                        // Handle other option-related settings
                                        $optionProps = ['allowOther', 'allowNone', 'optionsOrder'];
                                        foreach ($optionProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
                                        }
                                        break;
                                        
                                    case 'rating':
                                        // Handle rating settings
                                        $ratingProps = ['maxRating', 'showValues', 'icon'];
                                        foreach ($ratingProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
                                        }
                                        
                                        // Ensure maxRating is numeric
                                        if (isset($question['maxRating']) && !is_numeric($question['maxRating'])) {
                                            $question['maxRating'] = (int)$question['maxRating'];
                                        }
                                        
                                        // Default maxRating for rating questions
                                        if (!isset($question['maxRating'])) {
                                            $question['maxRating'] = 5;
                                        }
                                        break;
                                        
                                    case 'matrix':
                                        // Handle matrix settings
                                        $matrixProps = ['matrixType', 'rows', 'columns'];
                                        foreach ($matrixProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
                                        }
                                        break;
                                        
                                    case 'file-upload':
                                        // Handle file upload settings
                                        $fileProps = ['allowedTypes', 'maxFiles', 'maxSize'];
                                        foreach ($fileProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
                                        }
                                        break;
                                        
                                    case 'ranking':
                                        // Handle ranking settings
                                        if (!isset($question['options']) && isset($question['settings']['options'])) {
                                            $question['options'] = $question['settings']['options'];
                                        }
                                        break;
                                }
                                
                                Log::debug('Processed question type-specific settings', [
                                    'question_id' => $question['id'] ?? 'unknown',
                                    'type' => $question['type'],
                                    'settings_applied' => array_keys($question)
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        return $data;
    }
} 