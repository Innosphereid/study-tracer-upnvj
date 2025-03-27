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
    public function show(string $slug): View
    {
        Log::info('Displaying questionnaire form', ['slug' => $slug]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireBySlug($slug);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        abort_if($questionnaire->status !== 'published', 403, 'Kuesioner tidak tersedia');
        abort_if(!$questionnaire->isActive(), 403, 'Kuesioner sudah tidak aktif');
        
        $sections = $this->questionnaireService->getQuestionnaireSections($questionnaire->id);
        
        return view('questionnaire.form', compact('questionnaire', 'sections'));
    }
    
    /**
     * Store a response for the questionnaire.
     *
     * @param SubmitResponseRequest $request
     * @param string $slug
     * @return JsonResponse|RedirectResponse
     */
    public function submit(SubmitResponseRequest $request, string $slug): JsonResponse|RedirectResponse
    {
        Log::info('Submitting questionnaire response', ['slug' => $slug]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireBySlug($slug);
        
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
            return redirect()->route('form.thank-you', $slug)
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