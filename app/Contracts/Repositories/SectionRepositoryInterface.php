<?php

namespace App\Contracts\Repositories;

use App\Models\Section;
use Illuminate\Support\Collection;

interface SectionRepositoryInterface
{
    /**
     * Get all sections for a questionnaire.
     *
     * @param int $questionnaireId
     * @return Collection
     */
    public function getAllForQuestionnaire(int $questionnaireId): Collection;

    /**
     * Find section by ID.
     *
     * @param int $id
     * @return Section|null
     */
    public function find(int $id): ?Section;

    /**
     * Create a new section.
     *
     * @param array $data
     * @return Section
     */
    public function create(array $data): Section;

    /**
     * Update an existing section.
     *
     * @param int $id
     * @param array $data
     * @return Section
     */
    public function update(int $id, array $data): Section;

    /**
     * Delete a section.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Reorder sections.
     *
     * @param array $orderedIds An array of section IDs in the desired order
     * @return bool
     */
    public function reorder(array $orderedIds): bool;
}