<?php

namespace App\Contracts\Services;

use App\Models\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ResponseServiceInterface
{
    /**
     * Get responses with pagination for a questionnaire
     *
     * @param int $questionnaireId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedResponses(int $questionnaireId, int $perPage = 10): LengthAwarePaginator;
    
    /**
     * Get all responses for a questionnaire
     *
     * @param int $questionnaireId
     * @return Collection
     */
    public function getQuestionnaireResponses(int $questionnaireId): Collection;
    
    /**
     * Get a response by ID
     *
     * @param int $id
     * @return Response|null
     */
    public function getResponseById(int $id): ?Response;
    
    /**
     * Create a new response (start a questionnaire)
     *
     * @param int $questionnaireId
     * @param array $data
     * @return Response
     */
    public function startResponse(int $questionnaireId, array $data): Response;
    
    /**
     * Save answers for a response
     *
     * @param int $responseId
     * @param array $answers
     * @return bool
     */
    public function saveAnswers(int $responseId, array $answers): bool;
    
    /**
     * Complete a response
     *
     * @param int $responseId
     * @return bool
     */
    public function completeResponse(int $responseId): bool;
    
    /**
     * Delete a response
     *
     * @param int $id
     * @return bool
     */
    public function deleteResponse(int $id): bool;
    
    /**
     * Get answers for a response
     *
     * @param int $responseId
     * @return Collection
     */
    public function getResponseAnswers(int $responseId): Collection;
    
    /**
     * Get statistics for a questionnaire
     *
     * @param int $questionnaireId
     * @return array
     */
    public function getQuestionnaireStatistics(int $questionnaireId): array;
    
    /**
     * Export responses to CSV
     *
     * @param int $questionnaireId
     * @return string CSV content
     */
    public function exportResponsesToCSV(int $questionnaireId): string;
    
    /**
     * Find or create a response for a respondent
     * 
     * @param int $questionnaireId
     * @param string $identifier
     * @param array $data
     * @return Response
     */
    public function findOrCreateResponse(int $questionnaireId, string $identifier, array $data = []): Response;
} 