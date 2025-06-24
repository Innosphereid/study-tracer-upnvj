<?php

namespace App\Contracts\Repositories;

use App\Models\AnswerDetail;
use Illuminate\Database\Eloquent\Collection;

interface AnswerDetailRepositoryInterface extends RepositoryInterface
{
    /**
     * Get answer details by response ID.
     *
     * @param int $responseId
     * @param array $columns
     * @return Collection
     */
    public function getByResponseId(int $responseId, array $columns = ['*']): Collection;
    
    /**
     * Get answer details by question ID.
     *
     * @param int $questionId
     * @param array $columns
     * @return Collection
     */
    public function getByQuestionId(int $questionId, array $columns = ['*']): Collection;
    
    /**
     * Get answer details by questionnaire ID.
     *
     * @param int $questionnaireId
     * @param array $columns
     * @return Collection
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*']): Collection;
    
    /**
     * Get a specific answer detail by response ID and question ID.
     *
     * @param int $responseId
     * @param int $questionId
     * @param array $columns
     * @return AnswerDetail|null
     */
    public function getByResponseAndQuestionId(int $responseId, int $questionId, array $columns = ['*']): ?AnswerDetail;
    
    /**
     * Create or update answer details for a response.
     *
     * @param int $responseId
     * @param array $answerData Format: [questionId => answerValue, ...]
     * @param int $questionnaireId
     * @return bool
     */
    public function saveAnswers(int $responseId, array $answerData, int $questionnaireId): bool;
} 