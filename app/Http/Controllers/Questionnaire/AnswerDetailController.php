<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\AnswerDetailServiceInterface;
use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnswerDetailController extends Controller
{
    /**
     * @var AnswerDetailServiceInterface
     */
    protected $answerDetailService;
    
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * AnswerDetailController constructor.
     *
     * @param AnswerDetailServiceInterface $answerDetailService
     * @param QuestionnaireServiceInterface $questionnaireService
     */
    public function __construct(
        AnswerDetailServiceInterface $answerDetailService,
        QuestionnaireServiceInterface $questionnaireService
    ) {
        $this->answerDetailService = $answerDetailService;
        $this->questionnaireService = $questionnaireService;
    }
    
    /**
     * Get answer details by response ID.
     *
     * @param Request $request
     * @param int $responseId
     * @return JsonResponse
     */
    public function getByResponse(Request $request, int $responseId): JsonResponse
    {
        Log::info('Getting answer details by response', ['responseId' => $responseId]);
        
        $answers = $this->answerDetailService->getByResponseId($responseId);
        
        return response()->json([
            'success' => true,
            'data' => $answers
        ]);
    }
    
    /**
     * Get answer details by question ID.
     *
     * @param Request $request
     * @param int $questionId
     * @return JsonResponse
     */
    public function getByQuestion(Request $request, int $questionId): JsonResponse
    {
        $questionnaireId = $request->input('questionnaireId');
        $startDate = $request->input('start');
        $endDate = $request->input('end');
        
        Log::info('Getting answer details by question', [
            'questionId' => $questionId,
            'questionnaireId' => $questionnaireId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        // Validate questionnaire ownership if needed
        if ($questionnaireId) {
            $questionnaire = $this->questionnaireService->getQuestionnaireById((int)$questionnaireId);
            
            if (!$questionnaire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Questionnaire not found'
                ], 404);
            }
        }
        
        // Apply date filters if provided
        $filters = [];
        if ($startDate) {
            $filters['start_date'] = $startDate;
        }
        if ($endDate) {
            $filters['end_date'] = $endDate;
        }
        if ($questionnaireId) {
            $filters['questionnaire_id'] = (int)$questionnaireId;
        }
        
        $answers = $this->answerDetailService->getByQuestionId($questionId, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $answers
        ]);
    }
    
    /**
     * Get answer details by questionnaire ID.
     *
     * @param Request $request
     * @param int $questionnaireId
     * @return JsonResponse
     */
    public function getByQuestionnaire(Request $request, int $questionnaireId): JsonResponse
    {
        $startDate = $request->input('start');
        $endDate = $request->input('end');
        
        Log::info('Getting answer details by questionnaire', [
            'questionnaireId' => $questionnaireId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        // Validate questionnaire ownership if needed
        $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
        
        if (!$questionnaire) {
            return response()->json([
                'success' => false,
                'message' => 'Questionnaire not found'
            ], 404);
        }
        
        // Apply date filters if provided
        $filters = [];
        if ($startDate) {
            $filters['start_date'] = $startDate;
        }
        if ($endDate) {
            $filters['end_date'] = $endDate;
        }
        
        $answers = $this->answerDetailService->getByQuestionnaireId($questionnaireId, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $answers
        ]);
    }
    
    /**
     * Get answer details by response ID and question ID.
     *
     * @param Request $request
     * @param int $responseId
     * @param int $questionId
     * @return JsonResponse
     */
    public function getByResponseAndQuestion(Request $request, int $responseId, int $questionId): JsonResponse
    {
        Log::info('Getting answer details by response and question', [
            'responseId' => $responseId,
            'questionId' => $questionId
        ]);
        
        $answer = $this->answerDetailService->getByResponseAndQuestionId($responseId, $questionId);
        
        if (!$answer) {
            return response()->json([
                'success' => false,
                'message' => 'Answer not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $answer
        ]);
    }
} 