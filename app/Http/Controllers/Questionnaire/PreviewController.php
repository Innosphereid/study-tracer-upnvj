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
        if (!isset($data['welcomeScreen'])) {
            // First check if settings is a JSON string and parse it
            if (isset($data['settings']) && is_string($data['settings'])) {
                try {
                    $settings = json_decode($data['settings'], true);
                    Log::debug('Parsed settings string in PreviewController', [
                        'parsed_settings' => json_encode($settings, JSON_PRETTY_PRINT)
                    ]);
                    
                    // Replace the settings string with the parsed object
                    $data['settings'] = $settings;
                } catch (\Exception $e) {
                    Log::error('Failed to parse settings string in PreviewController', [
                        'error' => $e->getMessage(),
                        'settings_raw' => $data['settings']
                    ]);
                    $data['settings'] = [];
                }
            }
            
            // Now extract welcome screen from settings
            if (isset($data['settings']) && is_array($data['settings'])) {
                // Extract welcome screen from settings if available
                if (isset($data['settings']['welcomeScreen'])) {
                    $data['welcomeScreen'] = $data['settings']['welcomeScreen'];
                    Log::debug('Extracted welcomeScreen from settings', [
                        'welcome_screen' => json_encode($data['welcomeScreen'], JSON_PRETTY_PRINT)
                    ]);
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
            } else {
                // Default settings if no settings found
                $data['welcomeScreen'] = [
                    'title' => 'Selamat Datang',
                    'description' => 'Terima kasih telah berpartisipasi dalam kuesioner ini.'
                ];
                $data['thankYouScreen'] = [
                    'title' => 'Terima Kasih',
                    'description' => 'Terima kasih atas partisipasi Anda.'
                ];
                $data['showProgressBar'] = true;
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
                                'matrix' => 'matrix',
                                'likert' => 'likert',
                                'yes-no' => 'yes-no',
                                'slider' => 'slider',
                                'ranking' => 'ranking'
                            ];
                            
                            $question['type'] = $typeMap[$question['question_type']] ?? 'short-text';
                            
                            // Log the type mapping for debugging
                            Log::debug('Mapped question type', [
                                'question_id' => $question['id'] ?? 'unknown',
                                'backend_type' => $question['question_type'],
                                'frontend_type' => $question['type']
                            ]);
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
                                        
                                        // Ensure options array exists
                                        if (!isset($question['options'])) {
                                            $question['options'] = [];
                                        }
                                        
                                        // Handle special options (Other/None)
                                        // Handle allowOther option
                                        if (isset($question['settings']['allowOther'])) {
                                            $question['allowOther'] = (bool)$question['settings']['allowOther'];
                                            
                                            // Check if 'Lainnya' is already in the options
                                            $hasOtherOption = false;
                                            $otherOptionIndex = -1;
                                            
                                            if (is_array($question['options'])) {
                                                foreach ($question['options'] as $index => $opt) {
                                                    // Check if the required keys exist before accessing them
                                                    $optValue = $opt['value'] ?? '';
                                                    $optText = $opt['text'] ?? ($opt['label'] ?? '');
                                                    $optLabel = $opt['label'] ?? ($opt['text'] ?? '');
                                                    
                                                    if (($optValue === 'Lainnya' || $optValue === 'other') && 
                                                        ($optText === 'Lainnya' || $optLabel === 'Lainnya')) {
                                                        $hasOtherOption = true;
                                                        $otherOptionIndex = $index;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            // If "Other" option is not already in options array, make sure it's available in frontend
                                            if (!$hasOtherOption && $question['allowOther']) {
                                                Log::debug('Adding missing "Other" option to frontend', [
                                                    'question_id' => $question['id'] ?? 'unknown'
                                                ]);
                                                
                                                // Add the "Other" option to the options array
                                                if (!isset($question['options'])) {
                                                    $question['options'] = [];
                                                }
                                                
                                                $question['options'][] = [
                                                    'id' => 'other_option',
                                                    'text' => 'Lainnya',
                                                    'value' => 'Lainnya',
                                                    'isSpecial' => true
                                                ];
                                            } else if ($hasOtherOption && !$question['allowOther'] && $otherOptionIndex >= 0) {
                                                // Remove the option if allowOther is false but option exists
                                                array_splice($question['options'], $otherOptionIndex, 1);
                                            }
                                        }
                                        
                                        // Handle allowNone option
                                        if (isset($question['settings']['allowNone'])) {
                                            $question['allowNone'] = (bool)$question['settings']['allowNone'];
                                            
                                            // Check if 'Tidak Ada' is already in the options
                                            $hasNoneOption = false;
                                            $noneOptionIndex = -1;
                                            
                                            if (is_array($question['options'])) {
                                                foreach ($question['options'] as $index => $opt) {
                                                    // Check if the required keys exist before accessing them
                                                    $optValue = $opt['value'] ?? '';
                                                    $optText = $opt['text'] ?? ($opt['label'] ?? '');
                                                    $optLabel = $opt['label'] ?? ($opt['text'] ?? '');
                                                    
                                                    if (($optValue === 'Tidak Ada' || $optValue === 'none') && 
                                                        ($optText === 'Tidak Ada' || $optLabel === 'Tidak Ada')) {
                                                        $hasNoneOption = true;
                                                        $noneOptionIndex = $index;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            // If "None" option is not already in options array, make sure it's available in frontend
                                            if (!$hasNoneOption && $question['allowNone']) {
                                                Log::debug('Adding missing "None" option to frontend', [
                                                    'question_id' => $question['id'] ?? 'unknown'
                                                ]);
                                                
                                                // Add the "None" option to the options array
                                                if (!isset($question['options'])) {
                                                    $question['options'] = [];
                                                }
                                                
                                                $question['options'][] = [
                                                    'id' => 'none_option',
                                                    'text' => 'Tidak Ada',
                                                    'value' => 'Tidak Ada',
                                                    'isSpecial' => true
                                                ];
                                            } else if ($hasNoneOption && !$question['allowNone'] && $noneOptionIndex >= 0) {
                                                // Remove the option if allowNone is false but option exists
                                                array_splice($question['options'], $noneOptionIndex, 1);
                                            }
                                        }
                                        
                                        // Handle allowSelectAll option for checkbox questions
                                        if ($question['type'] === 'checkbox' && isset($question['settings']['allowSelectAll'])) {
                                            $question['allowSelectAll'] = (bool)$question['settings']['allowSelectAll'];
                                            
                                            // Check if 'Pilih Semua' is already in the options
                                            $hasSelectAllOption = false;
                                            $selectAllOptionIndex = -1;
                                            
                                            if (is_array($question['options'])) {
                                                foreach ($question['options'] as $index => $opt) {
                                                    // Check if the required keys exist before accessing them
                                                    $optValue = $opt['value'] ?? '';
                                                    $optText = $opt['text'] ?? ($opt['label'] ?? '');
                                                    $optLabel = $opt['label'] ?? ($opt['text'] ?? '');
                                                    
                                                    if (($optValue === 'Pilih Semua' || $optValue === 'selectAll') && 
                                                        ($optText === 'Pilih Semua' || $optLabel === 'Pilih Semua')) {
                                                        $hasSelectAllOption = true;
                                                        $selectAllOptionIndex = $index;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            // If "Select All" option is not already in options array, make sure it's available in frontend
                                            if (!$hasSelectAllOption && $question['allowSelectAll']) {
                                                Log::debug('Adding "Select All" option to frontend', [
                                                    'question_id' => $question['id'] ?? 'unknown'
                                                ]);
                                                
                                                // Add the "Select All" option to the options array
                                                if (!isset($question['options'])) {
                                                    $question['options'] = [];
                                                }
                                                
                                                // Insert Select All at the beginning of the array
                                                array_unshift($question['options'], [
                                                    'id' => 'select_all_option',
                                                    'text' => 'Pilih Semua',
                                                    'value' => 'Pilih Semua',
                                                    'isSpecial' => true,
                                                    'isSelectAll' => true
                                                ]);
                                            } else if ($hasSelectAllOption && !$question['allowSelectAll'] && $selectAllOptionIndex >= 0) {
                                                // Remove the option if allowSelectAll is false but option exists
                                                array_splice($question['options'], $selectAllOptionIndex, 1);
                                            }
                                        }
                                        
                                        // Normalize options format
                                        if (is_array($question['options'])) {
                                            $uniqueOptionsByValue = [];
                                            $specialValues = ['Lainnya', 'other', 'Tidak Ada', 'none', 'Pilih Semua', 'selectAll'];
                                            
                                            foreach ($question['options'] as $option) {
                                                $optValue = $option['value'] ?? '';
                                                $optId = $option['id'] ?? '';
                                                
                                                // For special options, only keep one instance
                                                if (in_array($optValue, $specialValues)) {
                                                    // If we already have this special option, skip this one
                                                    if (isset($uniqueOptionsByValue[$optValue])) {
                                                        continue;
                                                    }
                                                    
                                                    // If this is a database option (has numeric ID), prefer it
                                                    if (is_numeric($optId) && isset($uniqueOptionsByValue[$optValue])) {
                                                        // Replace the existing option with this one from DB
                                                        $uniqueOptionsByValue[$optValue] = $option;
                                                        continue;
                                                    }
                                                }
                                                
                                                // Add this option to our unique collection
                                                $uniqueOptionsByValue[$optValue] = $option;
                                            }
                                            
                                            // Replace options with deduplicated array
                                            $question['options'] = array_values($uniqueOptionsByValue);
                                            
                                            // Now normalize each option to ensure they have all required properties
                                            foreach ($question['options'] as &$option) {
                                                // Ensure each option has required properties
                                                if (!isset($option['id']) && isset($option['value'])) {
                                                    $option['id'] = 'option_' . $option['value'];
                                                } elseif (!isset($option['id'])) {
                                                    $option['id'] = 'option_' . mt_rand(1000, 9999);
                                                }
                                                
                                                // Ensure option has text
                                                if (!isset($option['text']) && isset($option['label'])) {
                                                    $option['text'] = $option['label'];
                                                } elseif (!isset($option['text']) && isset($option['value'])) {
                                                    $option['text'] = $option['value'];
                                                } elseif (!isset($option['text'])) {
                                                    // Provide a default text if neither label nor value exists
                                                    $option['text'] = 'Option ' . mt_rand(100, 999);
                                                }
                                                
                                                // Ensure option has value
                                                if (!isset($option['value']) && isset($option['text'])) {
                                                    $option['value'] = $option['text'];
                                                } elseif (!isset($option['value']) && isset($option['label'])) {
                                                    $option['value'] = $option['label'];
                                                } elseif (!isset($option['value'])) {
                                                    // Provide a default value if neither text nor label exists
                                                    $option['value'] = 'option_' . mt_rand(100, 999);
                                                }
                                                
                                                // If value follows the option_X pattern, replace it with the option text
                                                if (isset($option['value']) && isset($option['text']) && 
                                                    preg_match('/^option_\d+$/', $option['value'])) {
                                                    $option['value'] = $option['text'];
                                                }
                                                
                                                // Ensure option has label
                                                if (!isset($option['label'])) {
                                                    if (isset($option['text'])) {
                                                        $option['label'] = $option['text'];
                                                    } elseif (isset($option['value'])) {
                                                        $option['label'] = $option['value'];
                                                    } else {
                                                        $option['label'] = 'Option ' . mt_rand(100, 999);
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Handle other option-related settings
                                        $optionProps = ['optionsOrder'];
                                        foreach ($optionProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
                                        }
                                        break;
                                        
                                    case 'rating':
                                        // Handle rating settings
                                        $ratingProps = ['maxRating', 'showValues', 'icon', 'labels', 'minRating', 'maxRatingValue', 'stepValue'];
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
                                        
                                        // Check if this is actually a slider question based on settings
                                        if (isset($question['settings']['type']) && $question['settings']['type'] === 'slider') {
                                            $question['type'] = 'slider';
                                            
                                            // Copy slider-specific properties
                                            $sliderProps = ['min', 'max', 'step', 'showTicks', 'showLabels', 'labels', 'defaultValue'];
                                            foreach ($sliderProps as $prop) {
                                                if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                    $question[$prop] = $question['settings'][$prop];
                                                }
                                            }
                                        }
                                        break;
                                        
                                    case 'slider':
                                        // Handle slider settings
                                        $sliderProps = ['min', 'max', 'step', 'showTicks', 'showLabels', 'labels', 'defaultValue'];
                                        foreach ($sliderProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
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
                                        
                                        // Special handling to detect if this matrix is actually a likert question
                                        $isLikert = false;
                                        
                                        // First check direct settings as string
                                        if (isset($question['settings']) && is_string($question['settings'])) {
                                            try {
                                                $settings = json_decode($question['settings'], true);
                                                if (is_array($settings)) {
                                                    // Check if this is a likert question stored as matrix
                                                    if (isset($settings['type']) && $settings['type'] === 'likert') {
                                                        $isLikert = true;
                                                        $question['type'] = 'likert';
                                                        
                                                        // Copy likert properties
                                                        if (isset($settings['scale'])) {
                                                            $question['scale'] = $settings['scale'];
                                                        }
                                                        if (isset($settings['statements'])) {
                                                            $question['statements'] = $settings['statements'];
                                                        }
                                                        if (isset($settings['leftLabel'])) {
                                                            $question['leftLabel'] = $settings['leftLabel'];
                                                        }
                                                        if (isset($settings['rightLabel'])) {
                                                            $question['rightLabel'] = $settings['rightLabel'];
                                                        }
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                Log::warning('Failed to parse matrix settings', [
                                                    'error' => $e->getMessage()
                                                ]);
                                            }
                                        }
                                        
                                        // Then check settings as array
                                        if (!$isLikert && isset($question['settings']) && is_array($question['settings'])) {
                                            // Check if this is a likert question stored as matrix
                                            if (isset($question['settings']['type']) && $question['settings']['type'] === 'likert') {
                                                $isLikert = true;
                                                $question['type'] = 'likert';
                                                
                                                // Copy likert properties
                                                if (isset($question['settings']['scale'])) {
                                                    $question['scale'] = $question['settings']['scale'];
                                                }
                                                if (isset($question['settings']['statements'])) {
                                                    $question['statements'] = $question['settings']['statements'];
                                                }
                                                if (isset($question['settings']['leftLabel'])) {
                                                    $question['leftLabel'] = $question['settings']['leftLabel'];
                                                }
                                                if (isset($question['settings']['rightLabel'])) {
                                                    $question['rightLabel'] = $question['settings']['rightLabel'];
                                                }
                                            }
                                        }
                                        
                                        // Check for likert-specific structures even without the 'type' field
                                        if (!$isLikert) {
                                            $settingsObj = $question['settings'] ?? [];
                                            if (is_string($settingsObj)) {
                                                try {
                                                    $settingsObj = json_decode($settingsObj, true) ?: [];
                                                } catch (\Exception $e) {
                                                    $settingsObj = [];
                                                }
                                            }
                                            
                                            // If it has scale and statements, it's likely a likert question
                                            if ((isset($settingsObj['scale']) || isset($question['scale'])) && 
                                                (isset($settingsObj['statements']) || isset($question['statements']))) {
                                                $isLikert = true;
                                                $question['type'] = 'likert';
                                                
                                                if (!isset($question['scale']) && isset($settingsObj['scale'])) {
                                                    $question['scale'] = $settingsObj['scale'];
                                                }
                                                if (!isset($question['statements']) && isset($settingsObj['statements'])) {
                                                    $question['statements'] = $settingsObj['statements'];
                                                }
                                            }
                                        }
                                        
                                        // If we determined this is a likert question, ensure it has the necessary default fields
                                        if ($isLikert) {
                                            // Ensure scale is set - create default if needed
                                            if (!isset($question['scale']) || !is_array($question['scale']) || empty($question['scale'])) {
                                                $question['scale'] = [
                                                    ['value' => 1, 'label' => 'Sangat Tidak Setuju'],
                                                    ['value' => 2, 'label' => 'Tidak Setuju'],
                                                    ['value' => 3, 'label' => 'Netral'],
                                                    ['value' => 4, 'label' => 'Setuju'],
                                                    ['value' => 5, 'label' => 'Sangat Setuju']
                                                ];
                                            }
                                            
                                            // If no statements exist, create one from the text
                                            if ((!isset($question['statements']) || empty($question['statements'])) && isset($question['text'])) {
                                                $question['statements'] = [
                                                    [
                                                        'id' => 'statement-' . uniqid(),
                                                        'text' => $question['text']
                                                    ]
                                                ];
                                            }
                                            
                                            Log::debug('Converted matrix to likert', [
                                                'question_id' => $question['id'] ?? 'unknown',
                                                'has_scale' => isset($question['scale']),
                                                'has_statements' => isset($question['statements'])
                                            ]);
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
                                        
                                    case 'likert':
                                        // Handle likert settings
                                        $likertProps = ['scale', 'statements', 'leftLabel', 'rightLabel'];
                                        foreach ($likertProps as $prop) {
                                            if (!isset($question[$prop]) && isset($question['settings'][$prop])) {
                                                $question[$prop] = $question['settings'][$prop];
                                            }
                                        }
                                        
                                        // Parse settings if it's a string
                                        if (isset($question['settings']) && is_string($question['settings'])) {
                                            try {
                                                $settings = json_decode($question['settings'], true);
                                                if (is_array($settings)) {
                                                    if (!isset($question['scale']) && isset($settings['scale'])) {
                                                        $question['scale'] = $settings['scale'];
                                                    }
                                                    if (!isset($question['statements']) && isset($settings['statements'])) {
                                                        $question['statements'] = $settings['statements'];
                                                    }
                                                    if (!isset($question['leftLabel']) && isset($settings['leftLabel'])) {
                                                        $question['leftLabel'] = $settings['leftLabel'];
                                                    }
                                                    if (!isset($question['rightLabel']) && isset($settings['rightLabel'])) {
                                                        $question['rightLabel'] = $settings['rightLabel'];
                                                    }
                                                    
                                                    // If no statements exist, create one from the text
                                                    if ((!isset($question['statements']) || empty($question['statements'])) && isset($question['text'])) {
                                                        $question['statements'] = [
                                                            [
                                                                'id' => 'statement-' . uniqid(),
                                                                'text' => $question['text']
                                                            ]
                                                        ];
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                Log::warning('Failed to decode likert settings', [
                                                    'error' => $e->getMessage(),
                                                    'settings' => $question['settings']
                                                ]);
                                            }
                                        }
                                        
                                        // Ensure scale is set - create default if needed
                                        if (!isset($question['scale']) || !is_array($question['scale']) || empty($question['scale'])) {
                                            $question['scale'] = [
                                                ['value' => 1, 'label' => 'Sangat Tidak Setuju'],
                                                ['value' => 2, 'label' => 'Tidak Setuju'],
                                                ['value' => 3, 'label' => 'Netral'],
                                                ['value' => 4, 'label' => 'Setuju'],
                                                ['value' => 5, 'label' => 'Sangat Setuju']
                                            ];
                                        }
                                        
                                        // Ensure type is properly set to likert (may be overwridden elsewhere)
                                        $question['type'] = 'likert';
                                        
                                        Log::debug('Processed likert question', [
                                            'question_id' => $question['id'] ?? 'unknown',
                                            'has_scale' => isset($question['scale']),
                                            'has_statements' => isset($question['statements']),
                                            'scale_count' => isset($question['scale']) ? count($question['scale']) : 0,
                                            'statements_count' => isset($question['statements']) ? count($question['statements']) : 0
                                        ]);
                                        break;
                                        
                                    case 'yes-no':
                                        // Ensure yes-no question has the proper settings
                                        if (!isset($question['yesLabel'])) {
                                            $question['yesLabel'] = $question['settings']['yesLabel'] ?? 'Ya';
                                        }
                                        if (!isset($question['noLabel'])) {
                                            $question['noLabel'] = $question['settings']['noLabel'] ?? 'Tidak';
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