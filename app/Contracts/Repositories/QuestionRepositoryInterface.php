<?php

namespace App\Contracts\Repositories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

interface QuestionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get questions by section id
     *
     * @param int $sectionId
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function getBySectionId(int $sectionId, array $columns = ['*'], array $relations = []): Collection;
    
    /**
     * Reorder questions
     *
     * @param array $orderedIds Array of question ids in the order they should be
     * @return bool
     */
    public function reorder(array $orderedIds): bool;
    
    /**
     * Clone questions from one section to another
     *
     * @param int $sourceSectionId
     * @param int $targetSectionId
     * @return Collection
     */
    public function cloneFromSection(int $sourceSectionId, int $targetSectionId): Collection;
    
    /**
     * Get questions by type
     *
     * @param string $type
     * @param array $columns
     * @return Collection
     */
    public function getByType(string $type, array $columns = ['*']): Collection;
    
    /**
     * Get all options for a question
     *
     * @param int $questionId
     * @return Collection
     */
    public function getOptions(int $questionId): Collection;
    
    /**
     * Set options for a question
     *
     * @param int $questionId
     * @param array $options
     * @return bool
     */
    public function setOptions(int $questionId, array $options): bool;
} 