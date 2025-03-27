<?php

namespace App\Contracts\Services;

use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuestionnaireServiceInterface
{
    /**
     * Get questionnaires with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedQuestionnaires(int $perPage = 10): LengthAwarePaginator;
    
    /**
     * Get questionnaire by ID
     *
     * @param int $id
     * @return Questionnaire|null
     */
    public function getQuestionnaireById(int $id): ?Questionnaire;
    
    /**
     * Get questionnaire by slug
     *
     * @param string $slug
     * @return Questionnaire|null
     */
    public function getQuestionnaireBySlug(string $slug): ?Questionnaire;
    
    /**
     * Create a new questionnaire
     *
     * @param array $data
     * @param int $userId
     * @return Questionnaire
     */
    public function createQuestionnaire(array $data, int $userId): Questionnaire;
    
    /**
     * Update an existing questionnaire
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateQuestionnaire(int $id, array $data): bool;
    
    /**
     * Delete a questionnaire
     *
     * @param int $id
     * @return bool
     */
    public function deleteQuestionnaire(int $id): bool;
    
    /**
     * Clone a questionnaire
     *
     * @param int $id
     * @param array $attributes
     * @return Questionnaire|null
     */
    public function cloneQuestionnaire(int $id, array $attributes = []): ?Questionnaire;
    
    /**
     * Get active questionnaires
     *
     * @return Collection
     */
    public function getActiveQuestionnaires(): Collection;
    
    /**
     * Get template questionnaires
     *
     * @return Collection
     */
    public function getTemplateQuestionnaires(): Collection;
    
    /**
     * Get sections for a questionnaire
     *
     * @param int $questionnaireId
     * @return Collection
     */
    public function getQuestionnaireSections(int $questionnaireId): Collection;
    
    /**
     * Add a section to a questionnaire
     *
     * @param int $questionnaireId
     * @param array $sectionData
     * @return \App\Models\Section
     */
    public function addSection(int $questionnaireId, array $sectionData): \App\Models\Section;
    
    /**
     * Update a section
     *
     * @param int $sectionId
     * @param array $sectionData
     * @return bool
     */
    public function updateSection(int $sectionId, array $sectionData): bool;
    
    /**
     * Delete a section
     *
     * @param int $sectionId
     * @return bool
     */
    public function deleteSection(int $sectionId): bool;
    
    /**
     * Reorder sections
     *
     * @param array $orderedIds
     * @return bool
     */
    public function reorderSections(array $orderedIds): bool;
    
    /**
     * Add a question to a section
     *
     * @param int $sectionId
     * @param array $questionData
     * @return \App\Models\Question
     */
    public function addQuestion(int $sectionId, array $questionData): \App\Models\Question;
    
    /**
     * Update a question
     *
     * @param int $questionId
     * @param array $questionData
     * @return bool
     */
    public function updateQuestion(int $questionId, array $questionData): bool;
    
    /**
     * Delete a question
     *
     * @param int $questionId
     * @return bool
     */
    public function deleteQuestion(int $questionId): bool;
    
    /**
     * Reorder questions
     *
     * @param array $orderedIds
     * @return bool
     */
    public function reorderQuestions(array $orderedIds): bool;
    
    /**
     * Set options for a question
     *
     * @param int $questionId
     * @param array $options
     * @return bool
     */
    public function setQuestionOptions(int $questionId, array $options): bool;
    
    /**
     * Publish a questionnaire
     *
     * @param int $id
     * @param array $publishData
     * @return bool
     */
    public function publishQuestionnaire(int $id, array $publishData): bool;
    
    /**
     * Close a questionnaire
     *
     * @param int $id
     * @return bool
     */
    public function closeQuestionnaire(int $id): bool;
} 