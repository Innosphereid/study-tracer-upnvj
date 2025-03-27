<?php

namespace App\Services;

use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Repositories\QuestionRepositoryInterface;
use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class QuestionnaireService implements QuestionnaireServiceInterface
{
    /**
     * @var QuestionnaireRepositoryInterface
     */
    protected $questionnaireRepository;
    
    /**
     * @var SectionRepositoryInterface
     */
    protected $sectionRepository;
    
    /**
     * @var QuestionRepositoryInterface
     */
    protected $questionRepository;
    
    /**
     * QuestionnaireService constructor.
     *
     * @param QuestionnaireRepositoryInterface $questionnaireRepository
     * @param SectionRepositoryInterface $sectionRepository
     * @param QuestionRepositoryInterface $questionRepository
     */
    public function __construct(
        QuestionnaireRepositoryInterface $questionnaireRepository,
        SectionRepositoryInterface $sectionRepository,
        QuestionRepositoryInterface $questionRepository
    ) {
        $this->questionnaireRepository = $questionnaireRepository;
        $this->sectionRepository = $sectionRepository;
        $this->questionRepository = $questionRepository;
    }
    
    /**
     * @inheritDoc
     */
    public function getPaginatedQuestionnaires(int $perPage = 10): LengthAwarePaginator
    {
        Log::info('Getting paginated questionnaires', ['perPage' => $perPage]);
        return $this->questionnaireRepository->getPaginated($perPage, ['*'], ['user']);
    }
    
    /**
     * @inheritDoc
     */
    public function getQuestionnaireById(int $id): ?Questionnaire
    {
        Log::info('Getting questionnaire by ID', ['id' => $id]);
        return $this->questionnaireRepository->find($id);
    }
    
    /**
     * @inheritDoc
     */
    public function getQuestionnaireBySlug(string $slug): ?Questionnaire
    {
        Log::info('Getting questionnaire by slug', ['slug' => $slug]);
        return $this->questionnaireRepository->findBySlug($slug);
    }
    
    /**
     * @inheritDoc
     */
    public function createQuestionnaire(array $data, int $userId): Questionnaire
    {
        Log::info('Creating new questionnaire', ['userId' => $userId]);
        
        // Set user id
        $data['user_id'] = $userId;
        
        // Ensure status is draft if not specified
        if (!isset($data['status'])) {
            $data['status'] = 'draft';
        }
        
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireRepository->create($data);
        
        // If no sections provided, create a default one
        if (!isset($data['sections']) || empty($data['sections'])) {
            $this->addSection($questionnaire->id, [
                'title' => 'Default Section',
                'order' => 0
            ]);
        } else {
            // Create sections
            foreach ($data['sections'] as $order => $sectionData) {
                $sectionData['order'] = $order;
                $section = $this->addSection($questionnaire->id, $sectionData);
                
                // Create questions if provided
                if (isset($sectionData['questions']) && is_array($sectionData['questions'])) {
                    foreach ($sectionData['questions'] as $qOrder => $questionData) {
                        $questionData['order'] = $qOrder;
                        $question = $this->addQuestion($section->id, $questionData);
                        
                        // Create options if provided
                        if (isset($questionData['options']) && is_array($questionData['options'])) {
                            $this->setQuestionOptions($question->id, $questionData['options']);
                        }
                    }
                }
            }
        }
        
        Log::info('Questionnaire created successfully', ['id' => $questionnaire->id, 'title' => $questionnaire->title]);
        
        return $questionnaire;
    }
    
    /**
     * @inheritDoc
     */
    public function updateQuestionnaire(int $id, array $data): bool
    {
        Log::info('Updating questionnaire', ['id' => $id]);
        
        // Remove sections and questions from the data as they will be handled separately
        $sectionData = $data['sections'] ?? null;
        unset($data['sections']);
        
        $updated = $this->questionnaireRepository->update($id, $data);
        
        // Update sections if provided
        if ($sectionData && is_array($sectionData)) {
            // Get existing sections
            $existingSections = $this->sectionRepository->getByQuestionnaireId($id);
            $existingSectionIds = $existingSections->pluck('id')->toArray();
            $updatedSectionIds = [];
            
            foreach ($sectionData as $order => $section) {
                if (isset($section['id']) && in_array($section['id'], $existingSectionIds)) {
                    // Update existing section
                    $section['order'] = $order;
                    $this->updateSection($section['id'], $section);
                    $updatedSectionIds[] = $section['id'];
                    
                    // Update questions if provided
                    if (isset($section['questions']) && is_array($section['questions'])) {
                        $existingQuestions = $this->questionRepository->getBySectionId($section['id']);
                        $existingQuestionIds = $existingQuestions->pluck('id')->toArray();
                        $updatedQuestionIds = [];
                        
                        foreach ($section['questions'] as $qOrder => $question) {
                            $question['order'] = $qOrder;
                            
                            if (isset($question['id']) && in_array($question['id'], $existingQuestionIds)) {
                                // Update existing question
                                $this->updateQuestion($question['id'], $question);
                                $updatedQuestionIds[] = $question['id'];
                                
                                // Update options if provided
                                if (isset($question['options']) && is_array($question['options'])) {
                                    $this->setQuestionOptions($question['id'], $question['options']);
                                }
                            } else {
                                // Add new question
                                $newQuestion = $this->addQuestion($section['id'], $question);
                                
                                // Add options if provided
                                if (isset($question['options']) && is_array($question['options'])) {
                                    $this->setQuestionOptions($newQuestion->id, $question['options']);
                                }
                            }
                        }
                        
                        // Delete questions that were not updated
                        $questionsToDelete = array_diff($existingQuestionIds, $updatedQuestionIds);
                        foreach ($questionsToDelete as $questionId) {
                            $this->deleteQuestion($questionId);
                        }
                    }
                } else {
                    // Add new section
                    $section['order'] = $order;
                    $section['questionnaire_id'] = $id;
                    $newSection = $this->addSection($id, $section);
                    
                    // Add questions if provided
                    if (isset($section['questions']) && is_array($section['questions'])) {
                        foreach ($section['questions'] as $qOrder => $question) {
                            $question['order'] = $qOrder;
                            $newQuestion = $this->addQuestion($newSection->id, $question);
                            
                            // Add options if provided
                            if (isset($question['options']) && is_array($question['options'])) {
                                $this->setQuestionOptions($newQuestion->id, $question['options']);
                            }
                        }
                    }
                }
            }
            
            // Delete sections that were not updated
            $sectionsToDelete = array_diff($existingSectionIds, $updatedSectionIds);
            foreach ($sectionsToDelete as $sectionId) {
                $this->deleteSection($sectionId);
            }
        }
        
        Log::info('Questionnaire updated successfully', ['id' => $id]);
        
        return $updated;
    }
    
    /**
     * @inheritDoc
     */
    public function deleteQuestionnaire(int $id): bool
    {
        Log::info('Deleting questionnaire', ['id' => $id]);
        return $this->questionnaireRepository->delete($id);
    }
    
    /**
     * @inheritDoc
     */
    public function cloneQuestionnaire(int $id, array $attributes = []): ?Questionnaire
    {
        Log::info('Cloning questionnaire', ['id' => $id]);
        return $this->questionnaireRepository->clone($id, $attributes);
    }
    
    /**
     * @inheritDoc
     */
    public function getActiveQuestionnaires(): Collection
    {
        Log::info('Getting active questionnaires');
        return $this->questionnaireRepository->getActive();
    }
    
    /**
     * @inheritDoc
     */
    public function getTemplateQuestionnaires(): Collection
    {
        Log::info('Getting template questionnaires');
        return $this->questionnaireRepository->getTemplates();
    }
    
    /**
     * @inheritDoc
     */
    public function getQuestionnaireSections(int $questionnaireId): Collection
    {
        Log::info('Getting sections for questionnaire', ['questionnaireId' => $questionnaireId]);
        return $this->sectionRepository->getByQuestionnaireId($questionnaireId);
    }
    
    /**
     * @inheritDoc
     */
    public function addSection(int $questionnaireId, array $sectionData): Section
    {
        Log::info('Adding section to questionnaire', ['questionnaireId' => $questionnaireId]);
        
        $sectionData['questionnaire_id'] = $questionnaireId;
        
        /** @var Section $section */
        $section = $this->sectionRepository->create($sectionData);
        
        return $section;
    }
    
    /**
     * @inheritDoc
     */
    public function updateSection(int $sectionId, array $sectionData): bool
    {
        Log::info('Updating section', ['sectionId' => $sectionId]);
        
        // Remove nested data
        unset($sectionData['questions']);
        
        return $this->sectionRepository->update($sectionId, $sectionData);
    }
    
    /**
     * @inheritDoc
     */
    public function deleteSection(int $sectionId): bool
    {
        Log::info('Deleting section', ['sectionId' => $sectionId]);
        return $this->sectionRepository->delete($sectionId);
    }
    
    /**
     * @inheritDoc
     */
    public function reorderSections(array $orderedIds): bool
    {
        Log::info('Reordering sections', ['sectionIds' => $orderedIds]);
        return $this->sectionRepository->reorder($orderedIds);
    }
    
    /**
     * @inheritDoc
     */
    public function addQuestion(int $sectionId, array $questionData): Question
    {
        Log::info('Adding question to section', ['sectionId' => $sectionId]);
        
        $questionData['section_id'] = $sectionId;
        
        /** @var Question $question */
        $question = $this->questionRepository->create($questionData);
        
        return $question;
    }
    
    /**
     * @inheritDoc
     */
    public function updateQuestion(int $questionId, array $questionData): bool
    {
        Log::info('Updating question', ['questionId' => $questionId]);
        
        // Remove nested data
        unset($questionData['options']);
        
        return $this->questionRepository->update($questionId, $questionData);
    }
    
    /**
     * @inheritDoc
     */
    public function deleteQuestion(int $questionId): bool
    {
        Log::info('Deleting question', ['questionId' => $questionId]);
        return $this->questionRepository->delete($questionId);
    }
    
    /**
     * @inheritDoc
     */
    public function reorderQuestions(array $orderedIds): bool
    {
        Log::info('Reordering questions', ['questionIds' => $orderedIds]);
        return $this->questionRepository->reorder($orderedIds);
    }
    
    /**
     * @inheritDoc
     */
    public function setQuestionOptions(int $questionId, array $options): bool
    {
        Log::info('Setting options for question', ['questionId' => $questionId]);
        return $this->questionRepository->setOptions($questionId, $options);
    }
    
    /**
     * Publish a questionnaire
     * 
     * @param int $id
     * @param array $publishData
     * @return bool
     */
    public function publishQuestionnaire(int $id, array $publishData): bool
    {
        try {
            Log::info('Publishing questionnaire', ['id' => $id, 'data' => $publishData]);
            
            // Validasi data
            if (isset($publishData['start_date']) && isset($publishData['end_date'])) {
                if (!empty($publishData['start_date']) && !empty($publishData['end_date'])) {
                    $startDate = new \DateTime($publishData['start_date']);
                    $endDate = new \DateTime($publishData['end_date']);
                    
                    if ($startDate > $endDate) {
                        Log::error('Invalid date range', [
                            'id' => $id,
                            'start_date' => $publishData['start_date'],
                            'end_date' => $publishData['end_date']
                        ]);
                        return false;
                    }
                }
            }
            
            // Update data kuesioner
            $updateData = [
                'status' => 'published',
                'start_date' => $publishData['start_date'] ?? now(),
                'end_date' => $publishData['end_date'] ?? null,
            ];
            
            // Jika ada perubahan slug atau title
            if (isset($publishData['slug']) && !empty($publishData['slug'])) {
                $updateData['slug'] = $publishData['slug'];
            }
            
            if (isset($publishData['title']) && !empty($publishData['title'])) {
                $updateData['title'] = $publishData['title'];
            }
            
            $updated = $this->questionnaireRepository->update($id, $updateData);
            
            if ($updated) {
                Log::info('Questionnaire published successfully', ['id' => $id]);
                return true;
            } else {
                Log::error('Failed to update questionnaire status', ['id' => $id]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception when publishing questionnaire', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * @inheritDoc
     */
    public function closeQuestionnaire(int $id): bool
    {
        Log::info('Closing questionnaire', ['id' => $id]);
        
        return $this->questionnaireRepository->update($id, [
            'status' => 'closed',
            'end_date' => now()
        ]);
    }
} 