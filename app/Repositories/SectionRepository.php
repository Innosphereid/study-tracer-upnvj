<?php

namespace App\Repositories;

use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Models\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SectionRepository implements SectionRepositoryInterface
{
    /**
     * Get all sections for a questionnaire.
     *
     * @param int $questionnaireId
     * @return Collection
     */
    public function getAllForQuestionnaire(int $questionnaireId): Collection
    {
        return Section::where('questionnaire_id', $questionnaireId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Find section by ID.
     *
     * @param int $id
     * @return Section|null
     */
    public function find(int $id): ?Section
    {
        return Section::with(['questions.options', 'questions.logic'])->find($id);
    }

    /**
     * Create a new section.
     *
     * @param array $data
     * @return Section
     */
    public function create(array $data): Section
    {
        // Get the current max order for the questionnaire
        $maxOrder = Section::where('questionnaire_id', $data['questionnaire_id'])
            ->max('order') ?? 0;
        
        // Create the section with the next order
        return Section::create([
            'questionnaire_id' => $data['questionnaire_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'order' => $data['order'] ?? ($maxOrder + 1),
        ]);
    }

    /**
     * Update an existing section.
     *
     * @param int $id
     * @param array $data
     * @return Section
     */
    public function update(int $id, array $data): Section
    {
        $section = Section::findOrFail($id);
        
        $section->update([
            'title' => $data['title'] ?? $section->title,
            'description' => $data['description'] ?? $section->description,
            'order' => $data['order'] ?? $section->order,
        ]);
        
        return $section;
    }

    /**
     * Delete a section.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $section = Section::findOrFail($id);
        
        // Get all sections from the same questionnaire to reorder them
        $questionnaireSections = Section::where('questionnaire_id', $section->questionnaire_id)
            ->where('id', '!=', $id)
            ->orderBy('order')
            ->get();
        
        DB::beginTransaction();
        
        try {
            // Delete the section
            $result = $section->delete();
            
            // Reorder remaining sections
            foreach ($questionnaireSections as $index => $otherSection) {
                $otherSection->update(['order' => $index + 1]);
            }
            
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reorder sections.
     *
     * @param array $orderedIds An array of section IDs in the desired order
     * @return bool
     */
    public function reorder(array $orderedIds): bool
    {
        if (empty($orderedIds)) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($orderedIds as $index => $sectionId) {
                Section::where('id', $sectionId)->update(['order' => $index + 1]);
            }
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}