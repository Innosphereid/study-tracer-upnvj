<?php

namespace App\Repositories;

use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Models\Option;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\QuestionLogic;
use App\Models\Section;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionnaireRepository implements QuestionnaireRepositoryInterface
{
    /**
     * Get all questionnaires.
     *
     * @param int|null $userId Optional user ID to filter by
     * @param array $filters Optional filters ['status', 'search', etc]
     * @return Collection
     */
    public function getAll(?int $userId = null, array $filters = []): Collection
    {
        $query = Questionnaire::query();

        // Filter by user ID if provided
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Apply status filter if provided
        if (isset($filters['status']) && in_array($filters['status'], ['draft', 'published', 'closed'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply template filter if provided
        if (isset($filters['is_template'])) {
            $query->where('is_template', (bool) $filters['is_template']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get paginated questionnaires.
     *
     * @param int $perPage Number of items per page
     * @param int|null $userId Optional user ID to filter by
     * @param array $filters Optional filters ['status', 'search', etc]
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 10, ?int $userId = null, array $filters = []): LengthAwarePaginator
    {
        $query = Questionnaire::query();

        // Filter by user ID if provided
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Apply status filter if provided
        if (isset($filters['status']) && in_array($filters['status'], ['draft', 'published', 'closed'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply template filter if provided
        if (isset($filters['is_template'])) {
            $query->where('is_template', (bool) $filters['is_template']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find questionnaire by ID.
     *
     * @param int $id
     * @return Questionnaire|null
     */
    public function find(int $id): ?Questionnaire
    {
        return Questionnaire::with(['sections.questions.options', 'sections.questions.logic'])->find($id);
    }

    /**
     * Find questionnaire by unique code.
     * 
     * @param string $code
     * @return Questionnaire|null
     */
    public function findByCode(string $code): ?Questionnaire
    {
        // Decode the code to get the questionnaire ID
        // This is a simple implementation - you might want to use a more sophisticated method
        $id = $this->decodeQuestionnaireCode($code);
        
        if (!$id) {
            return null;
        }
        
        return $this->find($id);
    }

    /**
     * Create a new questionnaire.
     *
     * @param array $data
     * @return Questionnaire
     */
    public function create(array $data): Questionnaire
    {
        DB::beginTransaction();
        
        try {
            // Create the questionnaire
            $questionnaire = Questionnaire::create([
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_template' => $data['is_template'] ?? false,
            ]);
            
            // Create a default section if none provided
            if (!isset($data['sections']) || empty($data['sections'])) {
                Section::create([
                    'questionnaire_id' => $questionnaire->id,
                    'title' => 'Bagian 1',
                    'order' => 1,
                ]);
            } else {
                // Create sections from provided data
                foreach ($data['sections'] as $index => $sectionData) {
                    $section = Section::create([
                        'questionnaire_id' => $questionnaire->id,
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => $index + 1,
                    ]);
                    
                    // Create questions for this section if provided
                    if (isset($sectionData['questions']) && !empty($sectionData['questions'])) {
                        foreach ($sectionData['questions'] as $qIndex => $questionData) {
                            $this->createQuestion($section->id, $questionData, $qIndex + 1);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return $questionnaire;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing questionnaire.
     *
     * @param int $id
     * @param array $data
     * @return Questionnaire
     */
    public function update(int $id, array $data): Questionnaire
    {
        $questionnaire = Questionnaire::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Update the questionnaire
            $questionnaire->update([
                'title' => $data['title'] ?? $questionnaire->title,
                'description' => $data['description'] ?? $questionnaire->description,
                'status' => $data['status'] ?? $questionnaire->status,
                'start_date' => $data['start_date'] ?? $questionnaire->start_date,
                'end_date' => $data['end_date'] ?? $questionnaire->end_date,
                'is_template' => $data['is_template'] ?? $questionnaire->is_template,
            ]);
            
            // Update sections if provided
            if (isset($data['sections']) && !empty($data['sections'])) {
                // Get existing section IDs for this questionnaire
                $existingSectionIds = $questionnaire->sections()->pluck('id')->toArray();
                $updatedSectionIds = [];
                
                foreach ($data['sections'] as $index => $sectionData) {
                    if (isset($sectionData['id']) && in_array($sectionData['id'], $existingSectionIds)) {
                        // Update existing section
                        $section = Section::find($sectionData['id']);
                        $section->update([
                            'title' => $sectionData['title'],
                            'description' => $sectionData['description'] ?? $section->description,
                            'order' => $index + 1,
                        ]);
                        
                        $updatedSectionIds[] = $section->id;
                    } else {
                        // Create new section
                        $section = Section::create([
                            'questionnaire_id' => $questionnaire->id,
                            'title' => $sectionData['title'],
                            'description' => $sectionData['description'] ?? null,
                            'order' => $index + 1,
                        ]);
                        
                        $updatedSectionIds[] = $section->id;
                    }
                    
                    // Update questions if provided
                    if (isset($sectionData['questions']) && !empty($sectionData['questions'])) {
                        $existingQuestionIds = $section->questions()->pluck('id')->toArray();
                        $updatedQuestionIds = [];
                        
                        foreach ($sectionData['questions'] as $qIndex => $questionData) {
                            if (isset($questionData['id']) && in_array($questionData['id'], $existingQuestionIds)) {
                                // Update existing question
                                $this->updateQuestion($questionData['id'], $questionData);
                                $updatedQuestionIds[] = $questionData['id'];
                            } else {
                                // Create new question
                                $question = $this->createQuestion($section->id, $questionData, $qIndex + 1);
                                $updatedQuestionIds[] = $question->id;
                            }
                        }
                        
                        // Delete questions that were not updated
                        foreach ($existingQuestionIds as $questionId) {
                            if (!in_array($questionId, $updatedQuestionIds)) {
                                Question::destroy($questionId);
                            }
                        }
                    }
                }
                
                // Delete sections that were not updated
                foreach ($existingSectionIds as $sectionId) {
                    if (!in_array($sectionId, $updatedSectionIds)) {
                        Section::destroy($sectionId);
                    }
                }
            }
            
            DB::commit();
            
            return $questionnaire->fresh(['sections.questions.options', 'sections.questions.logic']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a questionnaire.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Questionnaire::destroy($id) > 0;
    }

    /**
     * Update the status of a questionnaire.
     *
     * @param int $id
     * @param string $status
     * @return Questionnaire
     */
    public function updateStatus(int $id, string $status): Questionnaire
    {
        $questionnaire = Questionnaire::findOrFail($id);
        
        if (!in_array($status, ['draft', 'published', 'closed'])) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }
        
        $questionnaire->update(['status' => $status]);
        
        return $questionnaire;
    }

    /**
     * Get template questionnaires.
     * 
     * @return Collection
     */
    public function getTemplates(): Collection
    {
        return Questionnaire::where('is_template', true)->orderBy('title')->get();
    }

    /**
     * Clone a questionnaire.
     * 
     * @param int $id
     * @param int $userId
     * @return Questionnaire
     */
    public function clone(int $id, int $userId): Questionnaire
    {
        $questionnaire = Questionnaire::with(['sections.questions.options', 'sections.questions.logic'])->findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Clone the questionnaire
            $newQuestionnaire = $questionnaire->replicate();
            $newQuestionnaire->user_id = $userId;
            $newQuestionnaire->title = "Salinan dari " . $questionnaire->title;
            $newQuestionnaire->status = 'draft';
            $newQuestionnaire->save();
            
            // Clone the sections
            foreach ($questionnaire->sections as $section) {
                $newSection = $section->replicate();
                $newSection->questionnaire_id = $newQuestionnaire->id;
                $newSection->save();
                
                // Clone the questions
                foreach ($section->questions as $question) {
                    $newQuestion = $question->replicate();
                    $newQuestion->section_id = $newSection->id;
                    $newQuestion->save();
                    
                    // Clone the options
                    foreach ($question->options as $option) {
                        $newOption = $option->replicate();
                        $newOption->question_id = $newQuestion->id;
                        $newOption->save();
                    }
                    
                    // Clone the logic
                    foreach ($question->logic as $logic) {
                        $newLogic = $logic->replicate();
                        $newLogic->question_id = $newQuestion->id;
                        // Note: We need to handle the action_target mapping separately
                        // since it might refer to a question or section that has been cloned
                        $newLogic->save();
                    }
                }
            }
            
            DB::commit();
            
            return $newQuestionnaire;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get active questionnaires.
     * 
     * @return Collection
     */
    public function getActive(): Collection
    {
        return Questionnaire::active()->get();
    }

    /**
     * Generate a unique code for a questionnaire.
     * 
     * @param int $id
     * @return string
     */
    public function generateUniqueCode(int $id): string
    {
        $questionnaire = Questionnaire::findOrFail($id);
        
        // Create a base code using the questionnaire ID and a timestamp
        $rawCode = $id . '_' . time();
        
        // Generate a short hash
        $code = Str::slug(Str::substr(md5($rawCode), 0, 10));
        
        return $code;
    }

    /**
     * Decode a questionnaire code to get the ID.
     * 
     * @param string $code
     * @return int|null
     */
    private function decodeQuestionnaireCode(string $code): ?int
    {
        // This is a simple implementation
        // In a real app, you might want to look up the code in a dedicated table
        $questionnaires = Questionnaire::all();
        
        foreach ($questionnaires as $questionnaire) {
            if ($this->generateUniqueCode($questionnaire->id) === $code) {
                return $questionnaire->id;
            }
        }
        
        return null;
    }

    /**
     * Helper method to create a question with its options and logic.
     *
     * @param int $sectionId
     * @param array $questionData
     * @param int $order
     * @return Question
     */
    private function createQuestion(int $sectionId, array $questionData, int $order): Question
    {
        $question = Question::create([
            'section_id' => $sectionId,
            'question_type' => $questionData['question_type'],
            'title' => $questionData['title'],
            'description' => $questionData['description'] ?? null,
            'is_required' => $questionData['is_required'] ?? false,
            'order' => $order,
            'settings' => isset($questionData['settings']) ? json_encode($questionData['settings']) : null,
        ]);
        
        // Create options if provided
        if (isset($questionData['options']) && !empty($questionData['options'])) {
            foreach ($questionData['options'] as $oIndex => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'value' => $optionData['value'],
                    'label' => $optionData['label'],
                    'order' => $oIndex + 1,
                ]);
            }
        }
        
        // Create logic if provided
        if (isset($questionData['logic']) && !empty($questionData['logic'])) {
            foreach ($questionData['logic'] as $logicData) {
                QuestionLogic::create([
                    'question_id' => $question->id,
                    'condition_type' => $logicData['condition_type'],
                    'condition_value' => $logicData['condition_value'] ?? null,
                    'action_type' => $logicData['action_type'],
                    'action_target' => $logicData['action_target'],
                ]);
            }
        }
        
        return $question;
    }

    /**
     * Helper method to update a question with its options and logic.
     *
     * @param int $questionId
     * @param array $questionData
     * @return Question
     */
    private function updateQuestion(int $questionId, array $questionData): Question
    {
        $question = Question::findOrFail($questionId);
        
        $question->update([
            'question_type' => $questionData['question_type'] ?? $question->question_type,
            'title' => $questionData['title'] ?? $question->title,
            'description' => $questionData['description'] ?? $question->description,
            'is_required' => $questionData['is_required'] ?? $question->is_required,
            'settings' => isset($questionData['settings']) ? json_encode($questionData['settings']) : $question->settings,
        ]);
        
        // Update options if provided
        if (isset($questionData['options']) && !empty($questionData['options'])) {
            // Delete existing options
            $question->options()->delete();
            
            // Create new options
            foreach ($questionData['options'] as $oIndex => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'value' => $optionData['value'],
                    'label' => $optionData['label'],
                    'order' => $oIndex + 1,
                ]);
            }
        }
        
        // Update logic if provided
        if (isset($questionData['logic']) && !empty($questionData['logic'])) {
            // Delete existing logic
            $question->logic()->delete();
            
            // Create new logic
            foreach ($questionData['logic'] as $logicData) {
                QuestionLogic::create([
                    'question_id' => $question->id,
                    'condition_type' => $logicData['condition_type'],
                    'condition_value' => $logicData['condition_value'] ?? null,
                    'action_type' => $logicData['action_type'],
                    'action_target' => $logicData['action_target'],
                ]);
            }
        }
        
        return $question;
    }
}