<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Contracts\Services\ResponseServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Questionnaire\SubmitResponseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Models\Questionnaire;

class FormController extends Controller
{
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * @var ResponseServiceInterface
     */
    protected $responseService;
    
    /**
     * FormController constructor.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     * @param ResponseServiceInterface $responseService
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService, ResponseServiceInterface $responseService)
    {
        $this->questionnaireService = $questionnaireService;
        $this->responseService = $responseService;
    }
    
    /**
     * Display the questionnaire form for submission.
     *
     * @param string $slug
     * @return View
     */
    public function show($slug)
    {
        // Find questionnaire by slug
        $questionnaire = Questionnaire::where('slug', $slug)
            ->where('status', 'published')
            ->with(['sections.questions.options'])
            ->firstOrFail();

        // Log questionnaire data for debugging
        Log::debug('Questionnaire data for alumni view:', [
            'questionnaire_id' => $questionnaire->id,
            'sections_count' => $questionnaire->sections->count(),
            'questions_count' => $questionnaire->sections->sum(function($section) {
                return $section->questions->count();
            })
        ]);
        
        // Process sections and questions to ensure proper format for Vue app
        $sections = $questionnaire->sections->map(function ($section) {
            // Map section data
            $sectionData = [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'order' => $section->order,
                'questions' => []
            ];
            
            // Process questions
            $questions = $section->questions->map(function ($question) {
                // Ensure settings is a proper JSON object
                $settings = $question->settings;
                if (is_string($settings)) {
                    try {
                        $settings = json_decode($settings, true);
                    } catch (\Exception $e) {
                        Log::error('Failed to parse question settings:', [
                            'question_id' => $question->id,
                            'settings' => $question->settings,
                            'error' => $e->getMessage()
                        ]);
                        $settings = [];
                    }
                }
                
                // Ensure options is correctly formatted
                $options = [];
                if ($question->options && $question->options->count() > 0) {
                    $options = $question->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'value' => $option->value,
                            'label' => $option->label,
                            'order' => $option->order
                        ];
                    })->sortBy('order')->values()->toArray();
                }
                
                // Build complete question data
                return [
                    'id' => $question->id,
                    'text' => $question->title, // Use title as text for compatibility
                    'title' => $question->title, // Use original title field
                    'description' => $question->description,
                    'helpText' => $question->description, // Also include as helpText for compatibility
                    'type' => $question->question_type, // Primary type field
                    'question_type' => $question->question_type, // Also include original for compatibility
                    'is_required' => $question->is_required,
                    'required' => $question->is_required, // Also include as required for compatibility
                    'order' => $question->order,
                    'settings' => $settings,
                    'options' => $options
                ];
            })->sortBy('order')->values()->toArray();
            
            $sectionData['questions'] = $questions;
            return $sectionData;
        })->sortBy('order')->values()->toArray();
        
        // Debug the processed sections
        Log::debug('Processing questionnaire sections completed');
        
        return view('questionnaire.show', compact('questionnaire', 'sections'));
    }
    
    /**
     * Store a response for the questionnaire.
     *
     * @param SubmitResponseRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(SubmitResponseRequest $request): JsonResponse|RedirectResponse
    {
        $slug = $request->input('slug');
        Log::info('Submitting questionnaire response', ['slug' => $slug]);
        
        $questionnaire = null;
        
        if (is_numeric($slug)) {
            // If it's numeric, try to fetch by ID first
            $questionnaire = $this->questionnaireService->getQuestionnaireById((int)$slug);
        }
        
        // If not found by ID or not numeric, try by slug
        if (!$questionnaire) {
            $questionnaire = $this->questionnaireService->getQuestionnaireBySlug($slug);
        }
        
        if (!$questionnaire) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Kuesioner tidak ditemukan'], 404);
            }
            
            abort(404, 'Kuesioner tidak ditemukan');
        }
        
        if ($questionnaire->status !== 'published' || !$questionnaire->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Kuesioner tidak tersedia'], 403);
            }
            
            abort(403, 'Kuesioner tidak tersedia');
        }
        
        $validatedData = $request->validated();
        
        // Create or get existing response
        $identifier = $validatedData['respondent_identifier'] ?? request()->ip();
        $response = $this->responseService->findOrCreateResponse($questionnaire->id, $identifier);
        
        // Save answers
        $saved = $this->responseService->saveAnswers($response->id, $validatedData['answers']);
        
        // Mark response as completed
        $completed = $this->responseService->completeResponse($response->id);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $saved && $completed,
                'message' => ($saved && $completed) ? 'Terima kasih atas partisipasi Anda!' : 'Gagal menyimpan jawaban'
            ]);
        }
        
        if ($saved && $completed) {
            return redirect()->route('form.thank-you', $questionnaire->slug)
                ->with('success', 'Terima kasih atas partisipasi Anda!');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan jawaban']);
        }
    }
    
    /**
     * Display the thank you page after submitting the questionnaire.
     *
     * @param string $slug
     * @return View
     */
    public function thankYou(string $slug): View
    {
        Log::info('Displaying thank you page', ['slug' => $slug]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireBySlug($slug);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        return view('questionnaire.thank-you', compact('questionnaire'));
    }
} 