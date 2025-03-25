<?php

namespace App\Repositories;

use App\Contracts\Repositories\QuestionRepositoryInterface;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionLogic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuestionRepository implements QuestionRepositoryInterface
{
    /**
     * Get all questions for a section.
     *
     * @param int $sectionId
     * @return Collection
     */
    public function getAllForSection(int $sectionId): Collection
    {
        return Question::where('section_id', $sectionId)
            ->with(['options', 'logic'])
            ->orderBy('order')
            ->get();
    }

    /**
     * Find question by ID.
     *
     * @param int $id
     * @return Question|null
     */
    public function find(int $id): ?Question
    {
        return Question::with(['options', 'logic'])->find($id);
    }

    /**
     * Create a new question.
     *
     * @param array $data
     * @return Question
     */
    public function create(array $data): Question
    {
        DB::beginTransaction();
        
        try {
            // Get the current max order for the section
            $maxOrder = Question::where('section_id', $data['section_id'])
                ->max('order') ?? 0;
            
            // Create the question
            $question = Question::create([
                'section_id' => $data['section_id'],
                'question_type' => $data['question_type'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_required' => $data['is_required'] ?? false,
                'order' => $data['order'] ?? ($maxOrder + 1),
                'settings' => isset($data['settings']) ? json_encode($data['settings']) : null,
            ]);
            
            // Create options if provided
            if (isset($data['options']) && !empty($data['options'])) {
                foreach ($data['options'] as $index => $optionData) {
                    Option::create([
                        'question_id' => $question->id,
                        'value' => $optionData['value'],
                        'label' => $optionData['label'],
                        'order' => $optionData['order'] ?? ($index + 1),
                    ]);
                }
            }
            
            // Create logic if provided
            if (isset($data['logic']) && !empty($data['logic'])) {
                foreach ($data['logic'] as $logicData) {
                    QuestionLogic::create([
                        'question_id' => $question->id,
                        'condition_type' => $logicData['condition_type'],
                        'condition_value' => $logicData['condition_value'] ?? null,
                        'action_type' => $logicData['action_type'],
                        'action_target' => $logicData['action_target'],
                    ]);
                }
            }
            
            DB::commit();
            
            return $question->load(['options', 'logic']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing question.
     *
     * @param int $id
     * @param array $data
     * @return Question
     */
    public function update(int $id, array $data): Question
    {
        $question = Question::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Update the question
            $question->update([
                'question_type' => $data['question_type'] ?? $question->question_type,
                'title' => $data['title'] ?? $question->title,
                'description' => $data['description'] ?? $question->description,
                'is_required' => $data['is_required'] ?? $question->is_required,
                'order' => $data['order'] ?? $question->order,
                'settings' => isset($data['settings']) ? json_encode($data['settings']) : $question->settings,
            ]);
            
            // Update options if provided
            if (isset($data['options'])) {
                $this->updateOptions($id, $data['options']);
            }
            
            // Update logic if provided
            if (isset($data['logic'])) {
                $this->updateLogic($id, $data['logic']);
            }
            
            DB::commit();
            
            return $question->load(['options', 'logic']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a question.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $question = Question::findOrFail($id);
        
        // Get all questions from the same section to reorder them
        $sectionQuestions = Question::where('section_id', $question->section_id)
            ->where('id', '!=', $id)
            ->orderBy('order')
            ->get();
        
        DB::beginTransaction();
        
        try {
            // Delete the question (cascades to options and logic)
            $result = $question->delete();
            
            // Reorder remaining questions
            foreach ($sectionQuestions as $index => $otherQuestion) {
                $otherQuestion->update(['order' => $index + 1]);
            }
            
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reorder questions.
     *
     * @param array $orderedIds An array of question IDs in the desired order
     * @return bool
     */
    public function reorder(array $orderedIds): bool
    {
        if (empty($orderedIds)) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($orderedIds as $index => $questionId) {
                Question::where('id', $questionId)->update(['order' => $index + 1]);
            }
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get all options for a question.
     *
     * @param int $questionId
     * @return Collection
     */
    public function getOptions(int $questionId): Collection
    {
        return Option::where('question_id', $questionId)
            ->orderBy('order')
            ->get();
    }
    
    /**
     * Update options for a question.
     *
     * @param int $questionId
     * @param array $options
     * @return bool
     */
    public function updateOptions(int $questionId, array $options): bool
    {
        DB::beginTransaction();
        
        try {
            // Delete existing options
            Option::where('question_id', $questionId)->delete();
            
            // Create new options
            foreach ($options as $index => $optionData) {
                Option::create([
                    'question_id' => $questionId,
                    'value' => $optionData['value'],
                    'label' => $optionData['label'],
                    'order' => $optionData['order'] ?? ($index + 1),
                ]);
            }
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get logic for a question.
     *
     * @param int $questionId
     * @return Collection
     */
    public function getLogic(int $questionId): Collection
    {
        return QuestionLogic::where('question_id', $questionId)->get();
    }
    
    /**
     * Update logic for a question.
     *
     * @param int $questionId
     * @param array $logic
     * @return bool
     */
    public function updateLogic(int $questionId, array $logic): bool
    {
        DB::beginTransaction();
        
        try {
            // Delete existing logic
            QuestionLogic::where('question_id', $questionId)->delete();
            
            // Create new logic
            foreach ($logic as $logicData) {
                QuestionLogic::create([
                    'question_id' => $questionId,
                    'condition_type' => $logicData['condition_type'],
                    'condition_value' => $logicData['condition_value'] ?? null,
                    'action_type' => $logicData['action_type'],
                    'action_target' => $logicData['action_target'],
                ]);
            }
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}