<?php

namespace App\Contracts\Repositories;

use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuestionnaireRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated questionnaires
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 10, array $columns = ['*'], array $relations = []): LengthAwarePaginator;
    
    /**
     * Find questionnaire by slug
     *
     * @param string $slug
     * @param array $columns
     * @return Questionnaire|null
     */
    public function findBySlug(string $slug, array $columns = ['*']): ?Questionnaire;
    
    /**
     * Get active questionnaires
     *
     * @param array $columns
     * @return Collection
     */
    public function getActive(array $columns = ['*']): Collection;
    
    /**
     * Get questionnaires by user id
     *
     * @param int $userId
     * @param array $columns
     * @return Collection
     */
    public function getByUserId(int $userId, array $columns = ['*']): Collection;
    
    /**
     * Get template questionnaires
     *
     * @param array $columns
     * @return Collection
     */
    public function getTemplates(array $columns = ['*']): Collection;
    
    /**
     * Clone a questionnaire
     *
     * @param int $id
     * @param array $attributes
     * @return Questionnaire|null
     */
    public function clone(int $id, array $attributes = []): ?Questionnaire;
} 