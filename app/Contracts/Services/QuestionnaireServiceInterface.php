<?php

namespace App\Contracts\Services;

use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface QuestionnaireServiceInterface
{
    /**
     * Get all questionnaires for the current user or all if superadmin.
     *
     * @param array $filters Optional filters
     * @return Collection
     */
    public function getQuestionnaires(array $filters = []): Collection;

    /**
     * Get paginated questionnaires for the current user or all if superadmin.
     *
     * @param int $perPage Number of items per page
     * @param array $filters Optional filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedQuestionnaires(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    /**
     * Get a questionnaire by ID.
     *
     * @param int $id
     * @return Questionnaire|null
     */
    public function getQuestionnaire(int $id): ?Questionnaire;

    /**
     * Get a questionnaire by code.
     *
     * @param string $code
     * @return Questionnaire|null
     */
    public function getQuestionnaireByCode(string $code): ?Questionnaire;

    /**
     * Create a new questionnaire.
     *
     * @param array $data
     * @return Questionnaire
     */
    public function createQuestionnaire(array $data): Questionnaire;

    /**
     * Update an existing questionnaire.
     *
     * @param int $id
     * @param array $data
     * @return Questionnaire
     */
    public function updateQuestionnaire(int $id, array $data): Questionnaire;

    /**
     * Delete a questionnaire.
     *
     * @param int $id
     * @return bool
     */
    public function deleteQuestionnaire(int $id): bool;

    /**
     * Publish a questionnaire.
     *
     * @param int $id
     * @return Questionnaire
     */
    public function publishQuestionnaire(int $id): Questionnaire;

    /**
     * Close a questionnaire.
     *
     * @param int $id
     * @return Questionnaire
     */
    public function closeQuestionnaire(int $id): Questionnaire;

    /**
     * Clone a questionnaire.
     *
     * @param int $id
     * @return Questionnaire
     */
    public function cloneQuestionnaire(int $id): Questionnaire;

    /**
     * Get active questionnaires.
     *
     * @return Collection
     */
    public function getActiveQuestionnaires(): Collection;

    /**
     * Get template questionnaires.
     *
     * @return Collection
     */
    public function getTemplateQuestionnaires(): Collection;

    /**
     * Generate a public link for a questionnaire.
     *
     * @param int $id
     * @return string
     */
    public function generatePublicLink(int $id): string;

    /**
     * Create section in a questionnaire.
     *
     * @param int $questionnaireId
     * @param array $data
     * @return array
     */
    public function createSection(int $questionnaireId, array $data): array;

    /**
     * Update section.
     *
     * @param int $sectionId
     * @param array $data
     * @return array
     */
    public function updateSection(int $sectionId, array $data): array;

    /**
     * Delete section.
     *
     * @param int $sectionId
     * @return bool
     */
    public function deleteSection(int $sectionId): bool;

    /**
     * Reorder sections.
     *
     * @param int $questionnaireId
     * @param array $orderedIds
     * @return bool
     */
    public function reorderSections(int $questionnaireId, array $orderedIds): bool;

    /**
     * Create question in a section.
     *
     * @param int $sectionId
     * @param array $data
     * @return array
     */
    public function createQuestion(int $sectionId, array $data): array;

    /**
     * Update question.
     *
     * @param int $questionId
     * @param array $data
     * @return array
     */
    public function updateQuestion(int $questionId, array $data): array;

    /**
     * Delete question.
     *
     * @param int $questionId
     * @return bool
     */
    public function deleteQuestion(int $questionId): bool;

    /**
     * Reorder questions.
     *
     * @param int $sectionId
     * @param array $orderedIds
     * @return bool
     */
    public function reorderQuestions(int $sectionId, array $orderedIds): bool;

    /**
     * Start a response for a questionnaire.
     *
     * @param int $questionnaireId
     * @param Request $request
     * @return array
     */
    public function startResponse(int $questionnaireId, Request $request): array;

    /**
     * Save an answer for a response.
     *
     * @param int $responseId
     * @param int $questionId
     * @param mixed $value
     * @return bool
     */
    public function saveAnswer(int $responseId, int $questionId, $value): bool;

    /**
     * Complete a response.
     *
     * @param int $responseId
     * @return array
     */
    public function completeResponse(int $responseId): array;

    /**
     * Get statistics for a questionnaire.
     *
     * @param int $questionnaireId
     * @return array
     */
    public function getStatistics(int $questionnaireId): array;
}