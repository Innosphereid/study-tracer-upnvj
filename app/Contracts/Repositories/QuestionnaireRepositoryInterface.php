<?php

namespace App\Contracts\Repositories;

use App\Models\Questionnaire;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface QuestionnaireRepositoryInterface
{
    /**
     * Get all questionnaires.
     *
     * @param int $userId Optional user ID to filter by
     * @param array $filters Optional filters ['status', 'search', etc]
     * @return Collection
     */
    public function getAll(int $userId = null, array $filters = []): Collection;

    /**
     * Get paginated questionnaires.
     *
     * @param int $perPage Number of items per page
     * @param int $userId Optional user ID to filter by
     * @param array $filters Optional filters ['status', 'search', etc]
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 10, int $userId = null, array $filters = []): LengthAwarePaginator;

    /**
     * Find questionnaire by ID.
     *
     * @param int $id
     * @return Questionnaire|null
     */
    public function find(int $id): ?Questionnaire;

    /**
     * Find questionnaire by unique code.
     * 
     * @param string $code
     * @return Questionnaire|null
     */
    public function findByCode(string $code): ?Questionnaire;

    /**
     * Create a new questionnaire.
     *
     * @param array $data
     * @return Questionnaire
     */
    public function create(array $data): Questionnaire;

    /**
     * Update an existing questionnaire.
     *
     * @param int $id
     * @param array $data
     * @return Questionnaire
     */
    public function update(int $id, array $data): Questionnaire;

    /**
     * Delete a questionnaire.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Update the status of a questionnaire.
     *
     * @param int $id
     * @param string $status
     * @return Questionnaire
     */
    public function updateStatus(int $id, string $status): Questionnaire;

    /**
     * Get template questionnaires.
     * 
     * @return Collection
     */
    public function getTemplates(): Collection;

    /**
     * Clone a questionnaire.
     * 
     * @param int $id
     * @param int $userId
     * @return Questionnaire
     */
    public function clone(int $id, int $userId): Questionnaire;

    /**
     * Get active questionnaires.
     * 
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Generate a unique code for a questionnaire.
     * 
     * @param int $id
     * @return string
     */
    public function generateUniqueCode(int $id): string;
}