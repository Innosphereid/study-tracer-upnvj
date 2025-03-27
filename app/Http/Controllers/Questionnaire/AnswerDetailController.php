<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnswerDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnswerDetailController extends Controller
{
    /**
     * @var AnswerDetailRepositoryInterface
     */
    protected $answerDetailRepository;
    
    /**
     * AnswerDetailController constructor.
     *
     * @param AnswerDetailRepositoryInterface $answerDetailRepository
     */
    public function __construct(AnswerDetailRepositoryInterface $answerDetailRepository)
    {
        $this->answerDetailRepository = $answerDetailRepository;
    }
    
    /**
     * Get all answers for a response.
     *
     * @param int $responseId
     * @return JsonResponse
     */
    public function getByResponse(int $responseId): JsonResponse
    {
        Log::info('Getting answer details for response', ['responseId' => $responseId]);
        
        $answers = $this->answerDetailRepository->getByResponseId($responseId);
        
        return response()->json([
            'status' => 'success',
            'data' => AnswerDetailResource::collection($answers),
            'meta' => [
                'count' => $answers->count()
            ]
        ]);
    }
    
    /**
     * Get all answers for a question.
     *
     * @param int $questionId
     * @return JsonResponse
     */
    public function getByQuestion(int $questionId): JsonResponse
    {
        Log::info('Getting answer details for question', ['questionId' => $questionId]);
        
        $answers = $this->answerDetailRepository->getByQuestionId($questionId);
        
        return response()->json([
            'status' => 'success',
            'data' => AnswerDetailResource::collection($answers),
            'meta' => [
                'count' => $answers->count()
            ]
        ]);
    }
    
    /**
     * Get all answers for a questionnaire.
     *
     * @param int $questionnaireId
     * @return JsonResponse
     */
    public function getByQuestionnaire(int $questionnaireId): JsonResponse
    {
        Log::info('Getting answer details for questionnaire', ['questionnaireId' => $questionnaireId]);
        
        $answers = $this->answerDetailRepository->getByQuestionnaireId($questionnaireId);
        
        return response()->json([
            'status' => 'success',
            'data' => AnswerDetailResource::collection($answers),
            'meta' => [
                'count' => $answers->count()
            ]
        ]);
    }
    
    /**
     * Get specific answer for a response and question.
     *
     * @param int $responseId
     * @param int $questionId
     * @return JsonResponse
     */
    public function getByResponseAndQuestion(int $responseId, int $questionId): JsonResponse
    {
        Log::info('Getting specific answer detail', [
            'responseId' => $responseId,
            'questionId' => $questionId
        ]);
        
        $answer = $this->answerDetailRepository->getByResponseAndQuestionId($responseId, $questionId);
        
        if (!$answer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Answer not found'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => new AnswerDetailResource($answer)
        ]);
    }
} 