<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SectionController extends Controller
{
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * SectionController constructor.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
    }

    /**
     * Get sections with questions for a questionnaire
     *
     * @param Request $request
     * @param int $questionnaireId
     * @return JsonResponse
     */
    public function getByQuestionnaire(Request $request, int $questionnaireId): JsonResponse
    {
        Log::info('Getting sections for questionnaire', ['questionnaireId' => $questionnaireId]);
        
        try {
            // Check if the questionnaire exists and belongs to the user
            $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
            
            if (!$questionnaire) {
                Log::warning('Questionnaire not found', ['questionnaireId' => $questionnaireId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Kuesioner tidak ditemukan'
                ], 404);
            }
            
            // Load sections with questions and options
            $questionnaire->load([
                'sections' => function($query) {
                    $query->orderBy('order');
                },
                'sections.questions' => function($query) {
                    $query->orderBy('order');
                },
                'sections.questions.options' => function($query) {
                    $query->orderBy('order');
                }
            ]);
            
            // Transform sections into the expected format
            $sections = $questionnaire->sections->map(function ($section) {
                $sectionData = [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'order' => $section->order,
                    'questions' => []
                ];
                
                // Add questions
                $questions = $section->questions->map(function ($question) {
                    // Parse settings
                    $settings = $question->settings;
                    if (is_string($settings)) {
                        $settings = json_decode($settings, true);
                    }
                    
                    // Format options
                    $options = $question->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'text' => $option->text,
                            'value' => $option->value,
                            'order' => $option->order
                        ];
                    })->toArray();
                    
                    return [
                        'id' => $question->id,
                        'text' => $question->title,
                        'title' => $question->title,
                        'description' => $question->description,
                        'helpText' => $question->description,
                        'type' => $question->question_type,
                        'question_type' => $question->question_type,
                        'is_required' => (bool) $question->is_required,
                        'required' => (bool) $question->is_required,
                        'order' => $question->order,
                        'settings' => $settings ?? [],
                        'options' => $options
                    ];
                })->toArray();
                
                $sectionData['questions'] = $questions;
                return $sectionData;
            })->toArray();
            
            Log::info('Sections retrieved successfully', [
                'questionnaireId' => $questionnaireId,
                'sectionsCount' => count($sections)
            ]);
            
            return response()->json([
                'success' => true,
                'sections' => $sections
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving sections', [
                'questionnaireId' => $questionnaireId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data seksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
