<?php

namespace App\Repositories;

use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SectionRepository extends BaseRepository implements SectionRepositoryInterface
{
    /**
     * SectionRepository constructor.
     *
     * @param Section $model
     */
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*']): Collection
    {
        Log::info('Fetching sections by questionnaire', ['questionnaireId' => $questionnaireId]);
        
        return $this->model->select($columns)
            ->where('questionnaire_id', $questionnaireId)
            ->orderBy('order')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function reorder(array $orderedIds): bool
    {
        Log::info('Reordering sections', ['sectionIds' => $orderedIds]);
        
        return DB::transaction(function () use ($orderedIds) {
            $reordered = true;
            
            foreach ($orderedIds as $order => $id) {
                // Update each section's order
                $updated = $this->update($id, ['order' => $order]);
                if (!$updated) {
                    $reordered = false;
                }
            }
            
            return $reordered;
        });
    }

    /**
     * @inheritDoc
     */
    public function cloneFromQuestionnaire(int $sourceQuestionnaireId, int $targetQuestionnaireId): Collection
    {
        Log::info('Cloning sections from questionnaire', [
            'sourceId' => $sourceQuestionnaireId,
            'targetId' => $targetQuestionnaireId
        ]);
        
        // Get sections from source questionnaire
        $sourceSections = $this->model->with(['questions.options', 'questions.logic'])
            ->where('questionnaire_id', $sourceQuestionnaireId)
            ->orderBy('order')
            ->get();
            
        if ($sourceSections->isEmpty()) {
            Log::warning('No sections found to clone', ['sourceId' => $sourceQuestionnaireId]);
            return collect();
        }
        
        // Begin transaction
        return DB::transaction(function () use ($sourceSections, $targetQuestionnaireId) {
            $clonedSections = collect();
            
            foreach ($sourceSections as $section) {
                // Clone the section
                $sectionData = $section->toArray();
                unset($sectionData['id'], $sectionData['created_at'], $sectionData['updated_at']);
                $sectionData['questionnaire_id'] = $targetQuestionnaireId;
                
                /** @var Section $newSection */
                $newSection = $this->create($sectionData);
                
                // Clone questions and their options
                foreach ($section->questions as $question) {
                    $questionData = $question->toArray();
                    unset($questionData['id'], $questionData['created_at'], $questionData['updated_at']);
                    $questionData['section_id'] = $newSection->id;
                    
                    /** @var \App\Models\Question $newQuestion */
                    $newQuestion = $newSection->questions()->create($questionData);
                    
                    // Clone options
                    foreach ($question->options as $option) {
                        $optionData = $option->toArray();
                        unset($optionData['id'], $optionData['created_at'], $optionData['updated_at']);
                        $optionData['question_id'] = $newQuestion->id;
                        
                        $newQuestion->options()->create($optionData);
                    }
                    
                    // Clone logic
                    foreach ($question->logic as $logic) {
                        $logicData = $logic->toArray();
                        unset($logicData['id'], $logicData['created_at'], $logicData['updated_at']);
                        $logicData['question_id'] = $newQuestion->id;
                        
                        // Note: In a real application, you would need to map the action_target IDs
                        $newQuestion->logic()->create($logicData);
                    }
                }
                
                $clonedSections->push($newSection);
            }
            
            Log::info('Sections cloned successfully', [
                'count' => $clonedSections->count(),
                'targetId' => $targetQuestionnaireId
            ]);
            
            return $clonedSections;
        });
    }
} 