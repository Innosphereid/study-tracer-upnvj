<?php

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;

interface AnswerDetailServiceInterface
{
    /**
     * Get answer details by response ID.
     *
     * @param int $responseId
     * @return Collection
     */
    public function getByResponseId(int $responseId): Collection;
    
    /**
     * Get answer details by question ID with optional filters.
     *
     * @param int $questionId
     * @param array $filters Optional filters like start_date, end_date, questionnaire_id
     * @return Collection
     */
    public function getByQuestionId(int $questionId, array $filters = []): Collection;
    
    /**
     * Get answer details by questionnaire ID with optional filters.
     *
     * @param int $questionnaireId
     * @param array $filters Optional filters like start_date, end_date
     * @return Collection
     */
    public function getByQuestionnaireId(int $questionnaireId, array $filters = []): Collection;
    
    /**
     * Get specific answer detail by response ID and question ID.
     *
     * @param int $responseId
     * @param int $questionId
     * @return mixed
     */
    public function getByResponseAndQuestionId(int $responseId, int $questionId);
} 