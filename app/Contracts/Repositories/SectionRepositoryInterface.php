<?php

namespace App\Contracts\Repositories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

interface SectionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get sections by questionnaire id
     *
     * @param int $questionnaireId
     * @param array $columns
     * @return Collection
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*']): Collection;
    
    /**
     * Reorder sections
     *
     * @param array $orderedIds Array of section ids in the order they should be
     * @return bool
     */
    public function reorder(array $orderedIds): bool;
    
    /**
     * Clone sections from one questionnaire to another
     *
     * @param int $sourceQuestionnaireId
     * @param int $targetQuestionnaireId
     * @return Collection
     */
    public function cloneFromQuestionnaire(int $sourceQuestionnaireId, int $targetQuestionnaireId): Collection;
} 