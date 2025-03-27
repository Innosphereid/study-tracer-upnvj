<?php

namespace App\Repositories;

use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Models\AnswerDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnswerDetailRepository extends BaseRepository implements AnswerDetailRepositoryInterface
{
    /**
     * AnswerDetailRepository constructor.
     *
     * @param AnswerDetail $model
     */
    public function __construct(AnswerDetail $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByResponseId(int $responseId, array $columns = ['*']): Collection
    {
        Log::info('Fetching answer details by response', ['responseId' => $responseId]);
        
        return $this->model->select($columns)
            ->where('response_id', $responseId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByQuestionId(int $questionId, array $columns = ['*']): Collection
    {
        Log::info('Fetching answer details by question', ['questionId' => $questionId]);
        
        return $this->model->select($columns)
            ->where('question_id', $questionId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*']): Collection
    {
        Log::info('Fetching answer details by questionnaire', ['questionnaireId' => $questionnaireId]);
        
        return $this->model->select($columns)
            ->where('questionnaire_id', $questionnaireId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByResponseAndQuestionId(int $responseId, int $questionId, array $columns = ['*']): ?AnswerDetail
    {
        Log::info('Fetching answer detail by response and question', [
            'responseId' => $responseId,
            'questionId' => $questionId
        ]);
        
        return $this->model->select($columns)
            ->where('response_id', $responseId)
            ->where('question_id', $questionId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function saveAnswers(int $responseId, array $answerData, int $questionnaireId): bool
    {
        Log::info('Saving answer details', [
            'responseId' => $responseId,
            'questionnaireId' => $questionnaireId,
            'answerCount' => count($answerData)
        ]);
        
        return DB::transaction(function () use ($responseId, $answerData, $questionnaireId) {
            $success = true;
            
            foreach ($answerData as $questionId => $answerValue) {
                // Check if answer already exists
                $existingAnswer = $this->getByResponseAndQuestionId($responseId, $questionId);
                
                if ($existingAnswer) {
                    // Update existing answer
                    $updated = $this->update($existingAnswer->id, [
                        'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                        'answer_data' => is_array($answerValue) ? $answerValue : null
                    ]);
                    
                    if (!$updated) {
                        $success = false;
                        Log::error('Failed to update answer detail', [
                            'answerId' => $existingAnswer->id,
                            'questionId' => $questionId
                        ]);
                    }
                } else {
                    // Create new answer
                    $result = $this->create([
                        'response_id' => $responseId,
                        'question_id' => $questionId,
                        'questionnaire_id' => $questionnaireId,
                        'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                        'answer_data' => is_array($answerValue) ? $answerValue : null
                    ]);
                    
                    if (!$result) {
                        $success = false;
                        Log::error('Failed to create answer detail', [
                            'responseId' => $responseId,
                            'questionId' => $questionId
                        ]);
                    }
                }
            }
            
            return $success;
        });
    }
} 