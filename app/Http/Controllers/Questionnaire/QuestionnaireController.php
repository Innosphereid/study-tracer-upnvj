<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Questionnaire\PublishQuestionnaireRequest;
use App\Http\Requests\Questionnaire\StoreQuestionnaireRequest;
use App\Http\Requests\Questionnaire\UpdateQuestionnaireRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Questionnaire;
use App\Models\Response;
use Carbon\Carbon;
use App\Services\QuestionnaireService;

class QuestionnaireController extends Controller
{
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * QuestionnaireController constructor.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
        // Controller middleware is defined in the routes file instead
    }
    
    /**
     * Display a listing of the questionnaires.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Log the filters
        Log::info('Filters received:', $request->all());
        
        // Determine active tab
        $activeTab = $request->query('tab', 'all');
        
        // Set up filters
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'period' => $request->query('period'),
            'is_template' => $request->query('is_template'),
            'sort' => $request->query('sort', 'newest'),
        ];
        
        // If tab is set, override status filter
        if ($activeTab !== 'all' && $activeTab !== 'template') {
            $filters['status'] = $activeTab;
        } elseif ($activeTab === 'template') {
            $filters['is_template'] = '1';
        }
        
        // Query base - Include withCount for needed statistics
        $query = Questionnaire::with('user')
            ->withCount('sections')
            ->withCount('responses')
            ->withCount(['sections as questions_count' => function($query) {
                $query->withCount('questions');
            }]);
        
        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['is_template'])) {
            $query->where('is_template', $filters['is_template']);
        }
        
        if (!empty($filters['period'])) {
            $now = Carbon::now();
            
            if ($filters['period'] === 'active') {
                $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->where('status', 'published');
            } elseif ($filters['period'] === 'upcoming') {
                $query->where('start_date', '>', $now)
                    ->where('status', 'published');
            } elseif ($filters['period'] === 'expired') {
                $query->where('end_date', '<', $now)
                    ->where('status', 'published');
            }
        }
        
        // Apply sorting
        if ($filters['sort'] === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($filters['sort'] === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($filters['sort'] === 'title_desc') {
            $query->orderBy('title', 'desc');
        } else {
            $query->orderBy('created_at', 'desc'); // default: newest
        }
        
        // Get counts for tabs
        $tabCounts = [
            'all' => Questionnaire::count(),
            'draft' => Questionnaire::where('status', 'draft')->count(),
            'published' => Questionnaire::where('status', 'published')->count(),
            'closed' => Questionnaire::where('status', 'closed')->count(),
            'template' => Questionnaire::where('is_template', true)->count(),
        ];
        
        // Get the counts for statistics
        $totalQuestionnaires = $tabCounts['all'];
        $activeQuestionnaires = Questionnaire::where('status', 'published')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->count();
        
        // Count responses
        $totalResponses = Response::count();
        
        // Calculate response rate
        $overallResponseRate = app(QuestionnaireService::class)->getOverallResponseRate();
        
        // Paginate the results
        $questionnaires = $query->paginate(9);
        
        // Calculate response rate for each questionnaire
        foreach ($questionnaires as $questionnaire) {
            $questionnaire->response_rate = $questionnaire->getResponseRate();
            
            // If the questions count is still not accurate with the withCount approach
            if (!isset($questionnaire->questions_count) || $questionnaire->questions_count <= 0) {
                // Load the sections with their questions relationship if needed
                if (!$questionnaire->relationLoaded('sections') || 
                    ($questionnaire->sections->isNotEmpty() && 
                     !$questionnaire->sections->first()->relationLoaded('questions'))) {
                    $questionnaire->load('sections.questions');
                }
                
                // Calculate the total questions manually
                $questionnaire->questions_count = $questionnaire->sections->sum(function ($section) {
                    return $section->questions->count();
                });
            }
        }
        
        return view('questionnaire.index', compact(
            'questionnaires',
            'filters',
            'totalQuestionnaires',
            'activeQuestionnaires',
            'totalResponses',
            'overallResponseRate',
            'tabCounts',
            'activeTab'
        ));
    }
    
    /**
     * Show the form for creating a new questionnaire.
     *
     * @return View
     */
    public function create(): View
    {
        Log::info('Displaying questionnaire creation form');
        
        $templates = $this->questionnaireService->getTemplateQuestionnaires();
        $initialData = [
            'title' => 'Kuesioner Baru',
            'sections' => []
        ];
        
        return view('questionnaire.create', compact('templates', 'initialData'));
    }
    
    /**
     * Store a newly created questionnaire in storage.
     *
     * @param StoreQuestionnaireRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreQuestionnaireRequest $request): JsonResponse|RedirectResponse
    {
        Log::info('Storing new questionnaire');
        
        $userId = Auth::id();
        $questionnaire = $this->questionnaireService->createQuestionnaire($request->validated(), $userId);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil dibuat',
                'id' => $questionnaire->id,
                'slug' => $questionnaire->slug
            ]);
        }
        
        return redirect()->route('questionnaires.edit', $questionnaire->id)
            ->with('success', 'Kuesioner berhasil dibuat.');
    }
    
    /**
     * Display the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        Log::info('Displaying questionnaire', ['id' => $id]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $sections = $this->questionnaireService->getQuestionnaireSections($id);
        
        return view('questionnaire.show', compact('questionnaire', 'sections'));
    }
    
    /**
     * Show the form for editing the specified questionnaire.
     * 
     * This method prepares the questionnaire data for the edit page.
     * It loads the questionnaire with its related sections, questions, and options,
     * and transforms the data structure to be compatible with the frontend builder component.
     * The JSON representation is used to ensure consistency with the frontend expectations.
     *
     * @param int $id - The ID of the questionnaire to edit
     * @return View - The view with the prepared questionnaire data
     */
    public function edit(int $id): View
    {
        Log::info('Displaying questionnaire edit form', ['id' => $id]);
        
        // Get full questionnaire with all related data
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        // Load sections with questions and options for complete data structure
        $questionnaire->load(['sections' => function($query) {
            $query->orderBy('order');
        }, 'sections.questions' => function($query) {
            $query->orderBy('order');
        }, 'sections.questions.options' => function($query) {
            $query->orderBy('order');
        }]);
        
        // Ensure we have the latest data after eager loading
        $questionnaire->refresh();
        
        // Debug information
        Log::debug('Questionnaire data structure for edit page', [
            'id' => $questionnaire->id,
            'sections_count' => $questionnaire->sections->count(),
            'has_settings' => !empty($questionnaire->settings),
            'has_questionnaire_json' => !empty($questionnaire->questionnaire_json)
        ]);
        
        // For each section, log the questions count
        foreach ($questionnaire->sections as $index => $section) {
            Log::debug("Section #{$index} data", [
                'id' => $section->id,
                'title' => $section->title,
                'questions_count' => $section->questions->count()
            ]);
        }
        
        // If questionnaire_json is available and complete, use it as it's more consistent with the frontend structure
        if (!empty($questionnaire->questionnaire_json)) {
            // Make sure questionnaire_json includes all sections and questions
            $jsonData = $questionnaire->questionnaire_json;
            
            // Verify if JSON has sections
            if (empty($jsonData['sections']) && $questionnaire->sections->isNotEmpty()) {
                Log::warning('questionnaire_json does not contain sections data, regenerating JSON');
                $questionnaire->storeAsJson();
                $questionnaire->refresh();
                $jsonData = $questionnaire->questionnaire_json;
            }
            
            // Debug questionnaire_json structure
            Log::debug('Using questionnaire_json structure', [
                'has_sections' => !empty($jsonData['sections']),
                'sections_count' => !empty($jsonData['sections']) ? count($jsonData['sections']) : 0,
                'first_section' => !empty($jsonData['sections']) ? (isset($jsonData['sections'][0]) ? json_encode($jsonData['sections'][0]) : 'Empty array') : 'N/A'
            ]);
            
            $questionnaire = $jsonData;
        } else {
            // Create a properly formatted data structure for the frontend
            $questionnaire->storeAsJson();
            $questionnaire->refresh();
            
            // Debug the newly created JSON structure
            Log::debug('Created new questionnaire_json structure', [
                'has_sections' => !empty($questionnaire->questionnaire_json['sections']),
                'sections_count' => !empty($questionnaire->questionnaire_json['sections']) ? 
                    count($questionnaire->questionnaire_json['sections']) : 0
            ]);
            
            $questionnaire = $questionnaire->questionnaire_json;
        }
        
        // Final verification of data structure
        if (empty($questionnaire['sections'])) {
            Log::error('No sections data available for questionnaire', ['id' => $id]);
        }
        
        return view('questionnaire.edit', compact('questionnaire'));
    }
    
    /**
     * Update the specified questionnaire in storage.
     *
     * @param UpdateQuestionnaireRequest $request
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function update(UpdateQuestionnaireRequest $request, int $id): JsonResponse|RedirectResponse
    {
        Log::info('Updating questionnaire', ['id' => $id]);
        
        $updated = $this->questionnaireService->updateQuestionnaire($id, $request->validated());
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $updated,
                'message' => $updated ? 'Kuesioner berhasil diperbarui' : 'Gagal memperbarui kuesioner'
            ]);
        }
        
        if ($updated) {
            return redirect()->route('questionnaires.edit', $id)
                ->with('success', 'Kuesioner berhasil diperbarui.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui kuesioner']);
        }
    }
    
    /**
     * Remove the specified questionnaire from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        Log::info('Deleting questionnaire', ['id' => $id]);
        
        $deleted = $this->questionnaireService->deleteQuestionnaire($id);
        
        if ($deleted) {
            return redirect()->route('questionnaires.index')
                ->with('success', 'Kuesioner berhasil dihapus');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus kuesioner']);
        }
    }
    
    /**
     * Preview the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function preview(int $id): View
    {
        Log::info('Previewing questionnaire', ['id' => $id]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        // Ensure we have the questionnaire_json data loaded
        if (empty($questionnaire->questionnaire_json)) {
            Log::info('Generating JSON representation for preview', ['id' => $id]);
            $questionnaire->storeAsJson();
            $questionnaire->refresh();
        }
        
        // Use the JSON representation for the preview as it's more complete
        $previewData = $questionnaire->questionnaire_json;
        
        // Add necessary structure expected by the frontend components
        if (is_array($previewData) && !isset($previewData['welcomeScreen']) && isset($previewData['settings'])) {
            // Extract welcome screen from settings if available
            if (isset($previewData['settings']['welcomeScreen'])) {
                $previewData['welcomeScreen'] = $previewData['settings']['welcomeScreen'];
            } else {
                // Create default welcome screen
                $previewData['welcomeScreen'] = [
                    'title' => 'Selamat Datang',
                    'description' => 'Terima kasih telah berpartisipasi dalam kuesioner ini.'
                ];
            }
            
            // Extract thank you screen from settings if available
            if (isset($previewData['settings']['thankYouScreen'])) {
                $previewData['thankYouScreen'] = $previewData['settings']['thankYouScreen'];
            } else {
                // Create default thank you screen
                $previewData['thankYouScreen'] = [
                    'title' => 'Terima Kasih',
                    'description' => 'Terima kasih atas partisipasi Anda.'
                ];
            }
            
            // Add progress bar setting
            if (!isset($previewData['showProgressBar']) && isset($previewData['settings']['showProgressBar'])) {
                $previewData['showProgressBar'] = $previewData['settings']['showProgressBar'];
            } else {
                $previewData['showProgressBar'] = true;
            }
            
            // Add page numbers setting
            if (!isset($previewData['showPageNumbers']) && isset($previewData['settings']['showPageNumbers'])) {
                $previewData['showPageNumbers'] = $previewData['settings']['showPageNumbers'];
            } else {
                $previewData['showPageNumbers'] = true;
            }
        }
        
        // Process sections to ensure each question has the expected structure
        if (isset($previewData['sections']) && is_array($previewData['sections'])) {
            foreach ($previewData['sections'] as &$section) {
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
                                'slider' => 'slider'
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
                                    $specialKeys = ['text', 'helpText', 'required', 'id', 'question_id', 'section_id', 'order', 'created_at', 'updated_at'];
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
                                        
                                        // Check if this is actually a slider question based on settings
                                        if (isset($question['settings']) && is_array($question['settings']) && 
                                            isset($question['settings']['type']) && $question['settings']['type'] === 'slider') {
                                            $question['type'] = 'slider';
                                            
                                            // Apply slider-specific properties
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
                                        $likertProps = ['scale', 'statements'];
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
                                                    
                                                    // If no statements exist, create one from the text
                                                    if ((!isset($question['statements']) || empty($question['statements'])) && isset($settings['text'])) {
                                                        $question['statements'] = [
                                                            [
                                                                'id' => 'statement-' . uniqid(),
                                                                'text' => $settings['text']
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
        
        Log::debug('Prepared questionnaire data for preview', [
            'id' => $id, 
            'sections_count' => isset($previewData['sections']) ? count($previewData['sections']) : 0
        ]);
        
        return view('questionnaire.preview', ['questionnaire' => $previewData]);
    }
    
    /**
     * Publish the specified questionnaire.
     *
     * @param PublishQuestionnaireRequest $request
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function publish(PublishQuestionnaireRequest $request, int $id): JsonResponse|RedirectResponse
    {
        Log::info('Publishing questionnaire - REQUEST DETAILS', [
            'id' => $id, 
            'method' => $request->method(),
            'all_data' => $request->all(),
            'validated_data' => $request->validated(),
            'user_id' => Auth::id(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'csrf_present' => $request->hasHeader('X-CSRF-TOKEN'),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
        ]);
        
        try {
            // Verifikasi bahwa kuesioner ada dan dimiliki oleh user yang melakukan request
            $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
            
            if (!$questionnaire) {
                Log::error('Questionnaire not found', ['id' => $id]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kuesioner tidak ditemukan'
                    ], 404);
                }
                
                return redirect()->back()->withErrors(['error' => 'Kuesioner tidak ditemukan']);
            }
            
            // Log detil kuesioner
            Log::info('Questionnaire found', [
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'user_id' => $questionnaire->user_id,
                'status' => $questionnaire->status
            ]);
            
            // Verifikasi kepemilikan (opsional, tergantung kebutuhan)
            // if ($questionnaire->user_id !== Auth::id()) {
            //     Log::error('Unauthorized publish attempt', ['id' => $id, 'user_id' => Auth::id()]);
            //     
            //     if ($request->expectsJson()) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Anda tidak memiliki izin untuk menerbitkan kuesioner ini'
            //         ], 403);
            //     }
            //     
            //     return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki izin untuk menerbitkan kuesioner ini']);
            // }
            
            // Publikasikan kuesioner
            $published = $this->questionnaireService->publishQuestionnaire($id, $request->validated());
            
            if ($published) {
                Log::info('Questionnaire published successfully', ['id' => $id]);
            } else {
                Log::error('Failed to publish questionnaire', ['id' => $id]);
            }
            
            if ($request->expectsJson()) {
                $updatedQuestionnaire = $this->questionnaireService->getQuestionnaireById($id);
                return response()->json([
                    'success' => $published,
                    'message' => $published ? 'Kuesioner berhasil dipublikasikan' : 'Gagal mempublikasikan kuesioner',
                    'questionnaire' => $published ? $updatedQuestionnaire : null,
                    'url' => $published ? route('form.show', $updatedQuestionnaire->slug) : null
                ]);
            }
            
            if ($published) {
                return redirect()->route('questionnaires.show', $id)
                    ->with('success', 'Kuesioner berhasil dipublikasikan.');
            } else {
                return redirect()->back()->withErrors(['error' => 'Gagal mempublikasikan kuesioner']);
            }
        } catch (\Exception $e) {
            Log::error('Exception when publishing questionnaire', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menerbitkan kuesioner: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menerbitkan kuesioner']);
        }
    }
    
    /**
     * Close the specified questionnaire.
     *
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function close(int $id): JsonResponse|RedirectResponse
    {
        Log::info('Closing questionnaire', ['id' => $id]);
        
        $closed = $this->questionnaireService->closeQuestionnaire($id);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $closed,
                'message' => $closed ? 'Kuesioner berhasil ditutup' : 'Gagal menutup kuesioner'
            ]);
        }
        
        if ($closed) {
            return redirect()->route('questionnaires.show', $id)
                ->with('success', 'Kuesioner berhasil ditutup.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menutup kuesioner']);
        }
    }
    
    /**
     * Clone the specified questionnaire.
     *
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function clone(int $id): JsonResponse|RedirectResponse
    {
        Log::info('Cloning questionnaire', ['id' => $id]);
        
        $clone = $this->questionnaireService->cloneQuestionnaire($id);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => (bool)$clone,
                'message' => $clone ? 'Kuesioner berhasil diduplikasi' : 'Gagal menduplikasi kuesioner',
                'id' => $clone ? $clone->id : null
            ]);
        }
        
        if ($clone) {
            return redirect()->route('questionnaires.edit', $clone->id)
                ->with('success', 'Kuesioner berhasil diduplikasi.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menduplikasi kuesioner']);
        }
    }
} 