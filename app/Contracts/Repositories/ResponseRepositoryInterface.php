<?php

namespace App\Contracts\Repositories;

use App\Models\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ResponseRepositoryInterface
{
    /**
     * Get all responses for a questionnaire.
     *
     * @param int $questionnaireId
     * @return Collection
     */
    public function getAllForQuestionnaire(int $questionnaireId): Collection;

    /**
     * Get paginated responses for a questionnaire.
     *
     * @param int $questionnaireId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedForQuestionnaire(int $questionnaireId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Find response by ID.
     *
     * @param int $id
     * @return Response|null
     */
    public function find(int $id): ?Response;

    /**
     * Create a new response.
     *
     * @param array $data
     * @return Response
     */
    public function create(array $data): Response;

    /**
     * Update an existing response.
     *
     * @param int $id
     * @param array $data
     * @return Response
     */
    public function update(int $id, array $data): Response;

    /**
     * Delete a response.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Complete a response.
     *
     * @param int $id
     * @return Response
     */
    public function complete(int $id): Response;

    /**
     * Save answer data for a response.
     *
     * @param int $responseId
     * @param int $questionId
     * @param string $value
     * @return bool
     */
    public function saveAnswer(int $responseId, int $questionId, string $value): bool;

    /**
     * Get all answers for a response.
     *
     * @param int $responseId
     * @return Collection
     */
    public function getAnswers(int $responseId): Collection;

    /**
     * Get response statistics for a questionnaire.
     *
     * @param int $questionnaireId
     * @return array
     */
    public function getStatistics(int $questionnaireId): array;
}