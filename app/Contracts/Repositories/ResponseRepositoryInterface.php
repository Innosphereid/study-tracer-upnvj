<?php

namespace App\Contracts\Repositories;

use App\Models\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ResponseRepositoryInterface extends RepositoryInterface
{
    /**
     * Get responses by questionnaire id
     *
     * @param int $questionnaireId
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*'], array $relations = []): Collection;
    
    /**
     * Get paginated responses by questionnaire id
     *
     * @param int $questionnaireId
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function getPaginatedByQuestionnaire(int $questionnaireId, int $perPage = 10, array $columns = ['*'], array $relations = []): LengthAwarePaginator;
    
    /**
     * Find response by respondent identifier and questionnaire id
     *
     * @param string $identifier
     * @param int $questionnaireId
     * @param array $columns
     * @return Response|null
     */
    public function findByRespondentAndQuestionnaire(string $identifier, int $questionnaireId, array $columns = ['*']): ?Response;
    
    /**
     * Get answer data for a response
     *
     * @param int $responseId
     * @return Collection
     */
    public function getAnswers(int $responseId): Collection;
    
    /**
     * Save answers for a response
     *
     * @param int $responseId
     * @param array $answers
     * @return bool
     */
    public function saveAnswers(int $responseId, array $answers): bool;
    
    /**
     * Mark response as completed
     *
     * @param int $responseId
     * @return bool
     */
    public function complete(int $responseId): bool;
    
    /**
     * Get response statistics for a questionnaire
     *
     * @param int $questionnaireId
     * @return array
     */
    public function getStatistics(int $questionnaireId): array;
} 