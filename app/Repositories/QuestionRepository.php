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
     * @inheritDoc
     */
    public function setOptions(int $questionId, array $options): bool
    {
        Log::info('Setting options for question', ['questionId' => $questionId, 'options_count' => count($options)]);
        
        /** @var Question|null $question */
        $question = $this->find($questionId);
        
        if (!$question) {
            Log::warning('Question not found when setting options', ['questionId' => $questionId]);
            return false;
        }
        
        // Only certain question types can have options
        if (!$question->hasOptions()) {
            Log::warning('Cannot set options for this question type', [
                'questionId' => $questionId, 
                'type' => $question->question_type
            ]);
            return false;
        }
        
        // Begin transaction
        return DB::transaction(function () use ($question, $options) {
            // Delete existing options
            $question->options()->delete();
            
            Log::info('Deleted existing options, creating new options', [
                'question_id' => $question->id,
                'options_count' => count($options)
            ]);
            
            // Create new options
            foreach ($options as $order => $option) {
                // Normalize option data
                $optionData = [];
                
                // Set order
                $optionData['order'] = is_numeric($order) ? $order : (isset($option['order']) ? $option['order'] : 0);
                
                // Handle value field (required)
                if (isset($option['value'])) {
                    $optionData['value'] = $option['value'];
                } elseif (isset($option['id'])) {
                    // Use id as fallback for value if needed
                    $optionData['value'] = (string) $option['id']; 
                } else {
                    // Generate a simple value if none provided
                    $optionData['value'] = 'option_' . ($order + 1);
                }
                
                // Handle label field (required)
                if (isset($option['label'])) {
                    $optionData['label'] = $option['label'];
                } elseif (isset($option['text'])) {
                    $optionData['label'] = $option['text'];
                } else {
                    // Use value as fallback for label
                    $optionData['label'] = $optionData['value'];
                }
                
                // Additional fields can be included
                foreach ($option as $key => $value) {
                    if (!in_array($key, ['order', 'value', 'label', 'text']) && !is_null($value)) {
                        $optionData[$key] = $value;
                    }
                }
                
                Log::info('Creating option', [
                    'question_id' => $question->id,
                    'option_data' => $optionData
                ]);
                
                // Create the option
                $question->options()->create($optionData);
            }
            
            return true;
        });
    }
} 