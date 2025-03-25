<?php

namespace App\Contracts\Repositories;

use App\Models\Question;
use Illuminate\Support\Collection;

interface QuestionRepositoryInterface
{
    /**
     * Get all questions for a section.
     *
     * @param int $sectionId
     * @return Collection
     */
    public function getAllForSection(int $sectionId): Collection;

    /**
     * Find question by ID.
     *
     * @param int $id
     * @return Question|null
     */
    public function find(int $id): ?Question;

    /**
     * Create a new question.
     *
     * @param array $data
     * @return Question
     */
    public function create(array $data): Question;

    /**
     * Update an existing question.
     *
     * @param int $id
     * @param array $data
     * @return Question
     */
    public function update(int $id, array $data): Question;

    /**
     * Delete a question.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Reorder questions.
     *
     * @param array $orderedIds An array of question IDs in the desired order
     * @return bool
     */
    public function reorder(array $orderedIds): bool;
    
    /**
     * Get all options for a question.
     *
     * @param int $questionId
     * @return Collection
     */
    public function getOptions(int $questionId): Collection;
    
    /**
     * Update options for a question.
     *
     * @param int $questionId
     * @param array $options
     * @return bool
     */
    public function updateOptions(int $questionId, array $options): bool;
    
    /**
     * Get logic for a question.
     *
     * @param int $questionId
     * @return Collection
     */
    public function getLogic(int $questionId): Collection;
    
    /**
     * Update logic for a question.
     *
     * @param int $questionId
     * @param array $logic
     * @return bool
     */
    public function updateLogic(int $questionId, array $logic): bool;
}