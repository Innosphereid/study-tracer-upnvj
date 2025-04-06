<?php

namespace App\Repositories;

use App\Contracts\Repositories\QuestionRepositoryInterface;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    /**
     * QuestionRepository constructor.
     *
     * @param Question $model
     */
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getBySectionId(int $sectionId, array $columns = ['*'], array $relations = []): Collection
    {
        Log::info('Fetching questions by section', ['sectionId' => $sectionId]);
        
        $query = $this->model->select($columns)
            ->where('section_id', $sectionId)
            ->orderBy('order');
            
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->get();
    }

    /**
     * @inheritDoc
     */
    public function reorder(array $orderedIds): bool
    {
        Log::info('Reordering questions', ['questionIds' => $orderedIds]);
        
        return DB::transaction(function () use ($orderedIds) {
            $reordered = true;
            
            foreach ($orderedIds as $order => $id) {
                // Update each question's order
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
    public function cloneFromSection(int $sourceSectionId, int $targetSectionId): Collection
    {
        Log::info('Cloning questions from section', [
            'sourceId' => $sourceSectionId,
            'targetId' => $targetSectionId
        ]);
        
        // Get questions from source section
        $sourceQuestions = $this->model->with(['options', 'logic'])
            ->where('section_id', $sourceSectionId)
            ->orderBy('order')
            ->get();
            
        if ($sourceQuestions->isEmpty()) {
            Log::warning('No questions found to clone', ['sourceId' => $sourceSectionId]);
            return collect();
        }
        
        // Begin transaction
        return DB::transaction(function () use ($sourceQuestions, $targetSectionId) {
            $clonedQuestions = collect();
            
            foreach ($sourceQuestions as $question) {
                // Clone the question
                $questionData = $question->toArray();
                unset($questionData['id'], $questionData['created_at'], $questionData['updated_at']);
                $questionData['section_id'] = $targetSectionId;
                
                /** @var Question $newQuestion */
                $newQuestion = $this->create($questionData);
                
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
                
                $clonedQuestions->push($newQuestion);
            }
            
            Log::info('Questions cloned successfully', [
                'count' => $clonedQuestions->count(),
                'targetId' => $targetSectionId
            ]);
            
            return $clonedQuestions;
        });
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type, array $columns = ['*']): Collection
    {
        Log::info('Fetching questions by type', ['type' => $type]);
        
        return $this->model->select($columns)
            ->where('question_type', $type)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getOptions(int $questionId): Collection
    {
        Log::info('Fetching options for question', ['questionId' => $questionId]);
        
        /** @var Question|null $question */
        $question = $this->find($questionId);
        
        if (!$question) {
            Log::warning('Question not found when fetching options', ['questionId' => $questionId]);
            return collect();
        }
        
        return $question->options()->orderBy('order')->get();
    }

    /**
     * Set the options for a question. This will delete existing options and replace them with new ones.
     *
     * @param int $questionId
     * @param array $options
     * @return bool
     */
    public function setOptions(int $questionId, array $options): bool
    {
        try {
            Log::info('Setting options for question', ['question_id' => $questionId, 'option_count' => count($options)]);
            
            // Get the question
            $question = $this->find($questionId);
            if (!$question) {
                Log::error('Question not found', ['question_id' => $questionId]);
                return false;
            }
            
            // Begin transaction
            DB::beginTransaction();
            
            // Delete existing options
            $question->options()->delete();
            
            // Create new options
            $order = 0;
            foreach ($options as $option) {
                // Allow for changing the order if specified
                $optionOrder = $option['order'] ?? $order;
                
                // Clean option data - remove any temporary IDs (non-numeric or UUIDs)
                $optionData = [
                    'question_id' => $questionId,
                    'value' => $option['value'] ?? 'Option',
                    'label' => $option['label'] ?? $option['text'] ?? $option['value'] ?? 'Option',
                    'order' => $optionOrder,
                ];
                
                // Create new option
                $question->options()->create($optionData);
                
                $order++;
            }
            
            // Commit transaction
            DB::commit();
            
            Log::info('Options set successfully', ['question_id' => $questionId, 'option_count' => count($options)]);
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            Log::error('Error setting options', [
                'question_id' => $questionId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
} 