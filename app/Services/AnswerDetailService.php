<?php

namespace App\Services;

use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Contracts\Services\AnswerDetailServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AnswerDetailService implements AnswerDetailServiceInterface
{
    /**
     * @var AnswerDetailRepositoryInterface
     */
    protected $answerDetailRepository;
    
    /**
     * AnswerDetailService constructor.
     *
     * @param AnswerDetailRepositoryInterface $answerDetailRepository
     */
    public function __construct(AnswerDetailRepositoryInterface $answerDetailRepository)
    {
        $this->answerDetailRepository = $answerDetailRepository;
    }
    
    /**
     * @inheritDoc
     */
    public function getByResponseId(int $responseId): Collection
    {
        Log::info('Getting answer details by response ID in service', ['responseId' => $responseId]);
        return $this->answerDetailRepository->getByResponseId($responseId);
    }
    
    /**
     * @inheritDoc
     */
    public function getByQuestionId(int $questionId, array $filters = []): Collection
    {
        Log::info('Getting answer details by question ID in service', [
            'questionId' => $questionId, 
            'filters' => $filters
        ]);
        
        // Get base results from repository
        $answerDetails = $this->answerDetailRepository->getByQuestionId($questionId);
        
        // Apply filters if needed
        if (!empty($filters)) {
            // Filter by questionnaire_id if provided
            if (!empty($filters['questionnaire_id'])) {
                $answerDetails = $answerDetails->where('questionnaire_id', $filters['questionnaire_id']);
            }
            
            // Apply date filtering
            if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
                $answerDetails = $answerDetails->filter(function ($answerDetail) use ($filters) {
                    $createdAt = $answerDetail->response->created_at;
                    
                    if (!empty($filters['start_date']) && $createdAt < $filters['start_date']) {
                        return false;
                    }
                    
                    if (!empty($filters['end_date']) && $createdAt > $filters['end_date']) {
                        return false;
                    }
                    
                    return true;
                });
            }
        }
        
        return $answerDetails;
    }
    
    /**
     * @inheritDoc
     */
    public function getByQuestionnaireId(int $questionnaireId, array $filters = []): Collection
    {
        Log::info('Getting answer details by questionnaire ID in service', [
            'questionnaireId' => $questionnaireId,
            'filters' => $filters
        ]);
        
        // Get base results from repository
        $answerDetails = $this->answerDetailRepository->getByQuestionnaireId($questionnaireId);
        
        // Apply date filtering if needed
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $answerDetails = $answerDetails->filter(function ($answerDetail) use ($filters) {
                $createdAt = $answerDetail->response->created_at;
                
                if (!empty($filters['start_date']) && $createdAt < $filters['start_date']) {
                    return false;
                }
                
                if (!empty($filters['end_date']) && $createdAt > $filters['end_date']) {
                    return false;
                }
                
                return true;
            });
        }
        
        return $answerDetails;
    }
    
    /**
     * @inheritDoc
     */
    public function getByResponseAndQuestionId(int $responseId, int $questionId)
    {
        Log::info('Getting specific answer detail in service', [
            'responseId' => $responseId,
            'questionId' => $questionId
        ]);
        
        return $this->answerDetailRepository->getByResponseAndQuestionId($responseId, $questionId);
    }
} 