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
        Log::info('Creating new questionnaire', ['userId' => $userId, 'data' => $data]);
        
        // Set user id
        $data['user_id'] = $userId;
        
        // Ensure status is draft if not specified
        if (!isset($data['status'])) {
            $data['status'] = 'draft';
        }

        // Siapkan sections data sebelum membuat questionnaire
        $sectionsData = null;
        
        // Extract sections from main data if exists
        if (isset($data['sections']) && is_array($data['sections'])) {
            $sectionsData = $data['sections'];
            unset($data['sections']);
        }
        
        // Extract sections from settings if exists (and main sections doesn't exist)
        if (!$sectionsData && isset($data['settings'])) {
            $settings = $data['settings'];
            
            // Jika settings adalah string JSON, decode dulu
            if (is_string($settings)) {
                $settingsData = json_decode($settings, true);
                if (isset($settingsData['sections']) && is_array($settingsData['sections'])) {
                    $sectionsData = $settingsData['sections'];
                    // Remove sections from settings to avoid duplication
                    unset($settingsData['sections']);
                    $data['settings'] = json_encode($settingsData);
                }
            } elseif (is_array($settings) && isset($settings['sections'])) {
                $sectionsData = $settings['sections'];
                unset($data['settings']['sections']);
            }
        }
        
        // Log the data we're about to process
        Log::info('Creating questionnaire with prepared data', [
            'has_sections' => isset($sectionsData),
            'sections_count' => isset($sectionsData) ? count($sectionsData) : 0
        ]);
        
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireRepository->create($data);
        
        // Process sections and questions (or create default section if none)
        if ($sectionsData && !empty($sectionsData)) {
            $this->processAndSaveSections($questionnaire->id, $sectionsData);
        } else {
            // Create default section if no sections provided
            Log::info('No sections found, creating default section');
            $this->addSection($questionnaire->id, [
                'title' => 'Default Section',
                'order' => 0
            ]);
        }
        
        Log::info('Questionnaire created successfully', ['id' => $questionnaire->id, 'title' => $questionnaire->title]);
        
        return $questionnaire;
    }
    
    /**
     * @inheritDoc
     */
    public function updateQuestionnaire(int $id, array $data): bool
    {
        Log::info('Updating questionnaire', ['id' => $id, 'data_keys' => array_keys($data)]);
        
        try {
            // Validate that the questionnaire exists
            $questionnaire = $this->questionnaireRepository->find($id);
            if (!$questionnaire) {
                Log::error('Cannot update: Questionnaire not found', ['id' => $id]);
                return false;
            }
            
            $updateData = [];
            
            // Update basic fields if provided
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
                Log::debug('Updating questionnaire title', ['title' => $data['title']]);
            }
            
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            
            if (isset($data['slug'])) {
                $updateData['slug'] = $data['slug'];
            }
            
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            
            if (isset($data['start_date'])) {
                $updateData['start_date'] = $data['start_date'];
            }
            
            if (isset($data['end_date'])) {
                $updateData['end_date'] = $data['end_date'];
            }
            
            if (isset($data['is_template'])) {
                $updateData['is_template'] = $data['is_template'];
            }
            
            if (isset($data['settings'])) {
                $updateData['settings'] = is_string($data['settings']) 
                    ? $data['settings'] 
                    : json_encode($data['settings']);
                Log::debug('Updating questionnaire settings');
            }
            
            // Update questionnaire with basic data first
            if (!empty($updateData)) {
                Log::debug('Updating questionnaire basic data', ['update_data' => $updateData]);
                $updated = $this->questionnaireRepository->update($id, $updateData);
                if (!$updated) {
                    Log::error('Failed to update questionnaire basic data', ['id' => $id]);
                    return false;
                }
            } else {
                $updated = true;
            }
            
            // Process sections and their questions if provided
            if (isset($data['sections']) && is_array($data['sections'])) {
                Log::info('Processing sections for update', [
                    'questionnaire_id' => $id,
                    'section_count' => count($data['sections'])
                ]);
                
                // Log the first section structure to help with debugging
                if (!empty($data['sections'])) {
                    Log::debug('First section data structure', [
                        'first_section' => $data['sections'][0],
                        'has_questions' => isset($data['sections'][0]['questions']),
                        'question_count' => isset($data['sections'][0]['questions']) ? count($data['sections'][0]['questions']) : 0
                    ]);
                    
                    // Log the first question if available
                    if (isset($data['sections'][0]['questions']) && !empty($data['sections'][0]['questions'])) {
                        Log::debug('First question data structure', [
                            'first_question' => $data['sections'][0]['questions'][0]
                        ]);
                    }
                }
                
                try {
                    // Process sections and questions
                    $this->processAndSaveSections($id, $data['sections']);
                } catch (\Exception $e) {
                    Log::error('Error processing sections', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return false;
                }
            }
            
            // Always update the JSON representation after any changes
            try {
                $questionnaire->refresh();
                $questionnaire->storeAsJson();
                Log::info('Successfully updated JSON representation for questionnaire', ['id' => $id]);
            } catch (\Exception $e) {
                Log::error('Error updating JSON representation', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                // Don't fail the entire update if only JSON storage fails
            }
            
            return $updated;
        } catch (\Exception $e) {
            Log::error('Unhandled exception in updateQuestionnaire', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
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
        Log::info('Adding question to section', [
            'sectionId' => $sectionId,
            'question_type' => $questionData['question_type'] ?? 'unknown'
        ]);
        
        try {
            // Get section to verify it exists and to get questionnaire_id
            $section = $this->sectionRepository->find($sectionId);
            if (!$section) {
                Log::error('Cannot add question: Section not found', ['sectionId' => $sectionId]);
                throw new \Exception('Section not found');
            }
            
            // Ensure section_id and questionnaire_id are set
            $questionData['section_id'] = $sectionId;
            $questionData['questionnaire_id'] = $section->questionnaire_id;
            
            // Log the data we're about to pass to the repository
            Log::debug('Creating question with data', [
                'question_data' => array_diff_key($questionData, ['options' => []]),
                'has_options' => isset($questionData['options'])
            ]);
            
            // Remove options from question data to avoid conflicts
            $options = isset($questionData['options']) ? $questionData['options'] : null;
            unset($questionData['options']);
            
            /** @var Question $question */
            $question = $this->questionRepository->create($questionData);
            
            // Handle options if present
            if ($options && is_array($options) && !empty($options)) {
                Log::debug('Setting options for new question', [
                    'question_id' => $question->id,
                    'option_count' => count($options)
                ]);
                
                $this->setQuestionOptions($question->id, $options);
            }
            
            // Update JSON representation
            if ($section->questionnaire) {
                try {
                    $section->questionnaire->storeAsJson();
                    Log::debug('Updated questionnaire JSON representation', [
                        'questionnaire_id' => $section->questionnaire_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update JSON representation', [
                        'questionnaire_id' => $section->questionnaire_id,
                        'exception' => get_class($e),
                        'message' => $e->getMessage()
                    ]);
                    // Don't fail the entire operation if only JSON storage fails
                }
            }
            
            Log::info('Question created successfully', [
                'question_id' => $question->id,
                'section_id' => $sectionId,
                'question_type' => $question->question_type
            ]);
            
            return $question;
        } catch (\Exception $e) {
            Log::error('Error creating question', [
                'section_id' => $sectionId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * @inheritDoc
     */
    public function updateQuestion(int $questionId, array $questionData): bool
    {
        Log::info('Updating question', ['questionId' => $questionId]);
        
        // Process settings if provided
        if (isset($questionData['settings']) && is_array($questionData['settings'])) {
            // Convert settings to JSON string if it's an array
            $questionData['settings'] = json_encode($questionData['settings']);
        }
        
        // Make sure is_required is properly set
        if (isset($questionData['required']) && !isset($questionData['is_required'])) {
            $questionData['is_required'] = $questionData['required'];
        }
        
        // Make sure title is properly set
        if (isset($questionData['text']) && !isset($questionData['title'])) {
            $questionData['title'] = $questionData['text'];
        }
        
        // Make sure description is properly set
        if (isset($questionData['helpText']) && !isset($questionData['description'])) {
            $questionData['description'] = $questionData['helpText'];
        }
        
        // Remove nested data
        unset($questionData['options']);
        
        $result = $this->questionRepository->update($questionId, $questionData);
        
        // Log the updated question for debugging
        if ($result) {
            $updatedQuestion = $this->questionRepository->find($questionId);
            Log::debug('Question updated successfully', [
                'id' => $questionId,
                'title' => $updatedQuestion->title,
                'is_required' => $updatedQuestion->is_required,
                'has_settings' => isset($updatedQuestion->settings),
            ]);
        }
        
        return $result;
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
     * @inheritDoc
     */
    public function publishQuestionnaire(int $id, array $publishData): bool
    {
        Log::info('Publishing questionnaire', ['id' => $id, 'data' => $publishData]);
        
        // Validate that the questionnaire exists
        $questionnaire = $this->questionnaireRepository->find($id);
        if (!$questionnaire) {
            Log::error('Cannot publish: Questionnaire not found', ['id' => $id]);
            return false;
        }

        // Process sections and questions from publishData
        if (isset($publishData['sections']) && is_array($publishData['sections']) && !empty($publishData['sections'])) {
            $this->processAndSaveSections($id, $publishData['sections']);
            
            // Setelah diproses, section tidak perlu disimpan lagi di settings untuk menghindari duplikasi
            if (isset($publishData['settings']) && is_array($publishData['settings']) && isset($publishData['settings']['sections'])) {
                unset($publishData['settings']['sections']);
            }
        } 
        // Jika tidak ada sections di publishData, periksa di settings
        elseif (isset($publishData['settings'])) {
            $settings = $publishData['settings'];
            
            // Jika settings adalah string JSON, decode dulu
            if (is_string($settings)) {
                $settings = json_decode($settings, true);
            }
            
            if (is_array($settings) && isset($settings['sections']) && is_array($settings['sections']) && !empty($settings['sections'])) {
                Log::info('Found sections in settings, processing them', [
                    'section_count' => count($settings['sections'])
                ]);
                
                $this->processAndSaveSections($id, $settings['sections']);
                
                // Hapus sections dari settings untuk menghindari duplikasi
                unset($settings['sections']);
                $publishData['settings'] = is_string($publishData['settings']) ? json_encode($settings) : $settings;
            }
        }
        
        // Sekarang, periksa sekali lagi apakah sections sudah dibuat
        $sections = $this->sectionRepository->getByQuestionnaireId($id);
        if ($sections->isEmpty()) {
            Log::warning('No sections found after attempting to process them', ['id' => $id]);
            
            // Buat section default jika tidak ada
            $this->addSection($id, [
                'title' => 'Default Section',
                'order' => 0
            ]);
        }
        
        // Update publish status and dates
        $updateData = [
            'status' => 'published'
        ];
        
        if (isset($publishData['start_date'])) {
            $updateData['start_date'] = $publishData['start_date'];
        }
        
        if (isset($publishData['end_date'])) {
            $updateData['end_date'] = $publishData['end_date'];
        }
        
        if (isset($publishData['title']) && $publishData['title']) {
            $updateData['title'] = $publishData['title'];
        }
        
        if (isset($publishData['slug']) && $publishData['slug']) {
            $updateData['slug'] = $publishData['slug'];
        }
        
        if (isset($publishData['settings'])) {
            $updateData['settings'] = is_string($publishData['settings']) 
                ? $publishData['settings'] 
                : json_encode($publishData['settings']);
        }
        
        // Update questionnaire and create JSON representation
        $result = $this->questionnaireRepository->update($id, $updateData);
        
        // Store the complete questionnaire as JSON
        $questionnaire->storeAsJson();
        
        return $result;
    }
    
    /**
     * Process and save sections and their questions
     * 
     * @param int $questionnaireId
     * @param array $sectionsData
     * @return void
     */
    private function processAndSaveSections(int $questionnaireId, array $sectionsData): void
    {
        Log::info('Processing sections for questionnaire', [
            'questionnaire_id' => $questionnaireId,
            'section_count' => count($sectionsData)
        ]);
        
        try {
            // Get existing sections to determine which ones to update vs create
            $existingSections = $this->sectionRepository->getByQuestionnaireId($questionnaireId);
            $existingSectionIds = $existingSections->pluck('id')->toArray();
            Log::debug('Existing section IDs', ['ids' => $existingSectionIds]);
            
            $updatedSectionIds = [];
            
            foreach ($sectionsData as $order => $sectionData) {
                // Log the section data to help with debugging
                Log::debug('Processing section', [
                    'order' => $order, 
                    'has_id' => isset($sectionData['id']),
                    'id_type' => isset($sectionData['id']) ? gettype($sectionData['id']) : 'not set',
                    'id_value' => isset($sectionData['id']) ? $sectionData['id'] : 'not set'
                ]);
                
                // Determine if we need to update or create
                $sectionId = null;
                if (isset($sectionData['id']) && is_numeric($sectionData['id']) && in_array($sectionData['id'], $existingSectionIds)) {
                    // Update existing section
                    $sectionId = $sectionData['id'];
                    $sectionData['order'] = $order;
                    
                    Log::debug('Updating existing section', ['section_id' => $sectionId, 'section_data' => array_diff_key($sectionData, ['questions' => ''])]);
                    
                    try {
                        $this->updateSection($sectionId, $sectionData);
                        $updatedSectionIds[] = $sectionId;
                        Log::info('Updated existing section', ['section_id' => $sectionId]);
                    } catch (\Exception $e) {
                        Log::error('Failed to update section', [
                            'section_id' => $sectionId,
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        // Continue processing other sections even if one fails
                    }
                } else {
                    // Create new section
                    $sectionData['order'] = $order;
                    
                    Log::debug('Creating new section', ['section_data' => array_diff_key($sectionData, ['questions' => ''])]);
                    
                    try {
                        $section = $this->addSection($questionnaireId, $sectionData);
                        $sectionId = $section->id;
                        $updatedSectionIds[] = $sectionId;
                        Log::info('Created new section', ['section_id' => $sectionId]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create section', [
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        // Continue processing other sections even if one fails
                        continue;
                    }
                }
                
                // Process questions for this section
                if (isset($sectionData['questions']) && is_array($sectionData['questions'])) {
                    Log::debug('Processing questions for section', [
                        'section_id' => $sectionId,
                        'question_count' => count($sectionData['questions'])
                    ]);
                    
                    try {
                        $this->processAndSaveQuestions($sectionId, $sectionData['questions']);
                        Log::info('Successfully processed questions for section', ['section_id' => $sectionId]);
                    } catch (\Exception $e) {
                        Log::error('Error processing questions for section', [
                            'section_id' => $sectionId,
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        // Continue processing other sections even if one fails
                    }
                } else {
                    Log::debug('No questions to process for section', ['section_id' => $sectionId]);
                }
            }
            
            // Remove sections that weren't updated (they've been deleted in the UI)
            $sectionsToDelete = array_diff($existingSectionIds, $updatedSectionIds);
            Log::debug('Sections to delete', ['ids' => $sectionsToDelete]);
            
            foreach ($sectionsToDelete as $sectionIdToDelete) {
                try {
                    $this->deleteSection($sectionIdToDelete);
                    Log::info('Deleted section that was not in updated data', ['section_id' => $sectionIdToDelete]);
                } catch (\Exception $e) {
                    Log::error('Failed to delete section', [
                        'section_id' => $sectionIdToDelete,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
            }
            
            // Generate JSON representation after processing all sections
            $questionnaire = $this->questionnaireRepository->find($questionnaireId);
            if ($questionnaire) {
                try {
                    $questionnaire->storeAsJson();
                    Log::info('Updated JSON representation after processing sections', ['questionnaire_id' => $questionnaireId]);
                } catch (\Exception $e) {
                    Log::error('Failed to update JSON representation after processing sections', [
                        'questionnaire_id' => $questionnaireId,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Unhandled exception in processAndSaveSections', [
                'questionnaire_id' => $questionnaireId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to be caught by the caller
        }
    }
    
    /**
     * Process and save questions and their options
     * 
     * @param int $sectionId
     * @param array $questionsData
     * @return void
     */
    private function processAndSaveQuestions(int $sectionId, array $questionsData): void
    {
        // Get the section to determine the questionnaire_id
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            Log::error('Cannot process questions: Section not found', ['sectionId' => $sectionId]);
            throw new \Exception('Section not found with ID: ' . $sectionId);
        }
        
        $questionnaireId = $section->questionnaire_id;
        
        Log::info('Processing questions for section', [
            'section_id' => $sectionId,
            'questionnaire_id' => $questionnaireId,
            'question_count' => count($questionsData)
        ]);
        
        // Log structure of first question to help with debugging
        if (!empty($questionsData)) {
            Log::debug('First question data structure', [
                'first_question' => $questionsData[0]
            ]);
        }
        
        // Get existing questions to determine which ones to update vs create
        $existingQuestions = $this->questionRepository->getBySectionId($sectionId);
        $existingQuestionIds = $existingQuestions->pluck('id')->toArray();
        Log::debug('Existing question IDs', ['ids' => $existingQuestionIds]);
        
        $updatedQuestionIds = [];
        
        try {
            foreach ($questionsData as $order => $questionData) {
                Log::debug('Processing question at order', [
                    'order' => $order,
                    'has_id' => isset($questionData['id']),
                    'id_type' => isset($questionData['id']) ? gettype($questionData['id']) : 'not set',
                    'id_value' => isset($questionData['id']) ? $questionData['id'] : 'not set'
                ]);
                
                // Convert data from frontend format to backend format
                $questionData = $this->normalizeQuestionData($questionData, $order);
                
                // Add questionnaire_id and section_id to the question data
                $questionData['questionnaire_id'] = $questionnaireId;
                $questionData['section_id'] = $sectionId;
                
                // Determine if we need to update or create
                if (isset($questionData['id']) && is_numeric($questionData['id']) && in_array($questionData['id'], $existingQuestionIds)) {
                    // Update existing question
                    $questionId = $questionData['id'];
                    Log::debug('Updating existing question', [
                        'question_id' => $questionId,
                        'question_data' => array_diff_key($questionData, ['options' => []])
                    ]);
                    
                    try {
                        $success = $this->updateQuestion($questionId, $questionData);
                        if (!$success) {
                            Log::error('Failed to update question', [
                                'question_id' => $questionId
                            ]);
                            continue;
                        }
                        
                        $updatedQuestionIds[] = $questionId;
                        
                        // Update options if provided
                        if (isset($questionData['options']) && is_array($questionData['options'])) {
                            Log::info('Setting options for question', [
                                'id' => $questionId,
                                'options_count' => count($questionData['options'])
                            ]);
                            
                            // Check if "Other" and "None" options should be included
                            $hasOtherOption = false;
                            $hasNoneOption = false;
                            
                            if (isset($questionData['allowOther']) && $questionData['allowOther']) {
                                $hasOtherOption = true;
                            } elseif (isset($questionData['settings']) && is_array($questionData['settings']) && 
                                      isset($questionData['settings']['allowOther']) && $questionData['settings']['allowOther']) {
                                $hasOtherOption = true;
                            }
                            
                            if (isset($questionData['allowNone']) && $questionData['allowNone']) {
                                $hasNoneOption = true;
                            } elseif (isset($questionData['settings']) && is_array($questionData['settings']) && 
                                      isset($questionData['settings']['allowNone']) && $questionData['settings']['allowNone']) {
                                $hasNoneOption = true;
                            }
                            
                            // The setQuestionOptions method in repository will handle adding "Other" and "None" options
                            // based on the settings of the question
                            $this->setQuestionOptions($questionId, $questionData['options']);
                        }
                        
                        Log::info('Updated existing question', ['question_id' => $questionId]);
                    } catch (\Exception $e) {
                        Log::error('Error updating question', [
                            'question_id' => $questionId,
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        // Continue with other questions
                    }
                } else {
                    // Create new question
                    try {
                        Log::debug('Creating new question', [
                            'section_id' => $sectionId,
                            'question_data' => array_diff_key($questionData, ['options' => []])
                        ]);
                        
                        $question = $this->addQuestion($sectionId, $questionData);
                        $questionId = $question->id;
                        $updatedQuestionIds[] = $questionId;
                        
                        // Create options if provided
                        if (isset($questionData['options']) && is_array($questionData['options'])) {
                            Log::debug('Setting options for new question', [
                                'question_id' => $questionId,
                                'option_count' => count($questionData['options'])
                            ]);
                            
                            // Check if "Other" and "None" options should be included
                            $hasOtherOption = false;
                            $hasNoneOption = false;
                            
                            if (isset($questionData['allowOther']) && $questionData['allowOther']) {
                                $hasOtherOption = true;
                            } elseif (isset($questionData['settings']) && is_array($questionData['settings']) && 
                                      isset($questionData['settings']['allowOther']) && $questionData['settings']['allowOther']) {
                                $hasOtherOption = true;
                            }
                            
                            if (isset($questionData['allowNone']) && $questionData['allowNone']) {
                                $hasNoneOption = true;
                            } elseif (isset($questionData['settings']) && is_array($questionData['settings']) && 
                                      isset($questionData['settings']['allowNone']) && $questionData['settings']['allowNone']) {
                                $hasNoneOption = true;
                            }
                            
                            // The setQuestionOptions method in repository will handle adding "Other" and "None" options
                            // based on the settings of the question
                            $this->setQuestionOptions($questionId, $questionData['options']);
                        }
                        
                        Log::info('Created new question', [
                            'question_id' => $questionId,
                            'question_type' => $questionData['question_type']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error creating question', [
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        // Continue with other questions
                    }
                }
            }
            
            // Remove questions that weren't updated (they've been deleted in the UI)
            $questionsToDelete = array_diff($existingQuestionIds, $updatedQuestionIds);
            Log::debug('Questions to delete', ['ids' => $questionsToDelete]);
            
            foreach ($questionsToDelete as $questionIdToDelete) {
                try {
                    $this->deleteQuestion($questionIdToDelete);
                    Log::info('Deleted question that was not in updated data', [
                        'question_id' => $questionIdToDelete
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting question', [
                        'question_id' => $questionIdToDelete,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    // Continue with other deletions
                }
            }
        } catch (\Exception $e) {
            Log::error('Unhandled exception in processAndSaveQuestions', [
                'section_id' => $sectionId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Normalize question data from frontend format to backend format
     * 
     * @param array $questionData
     * @param int $order
     * @return array
     */
    private function normalizeQuestionData(array $questionData, int $order): array
    {
        // Log the raw data coming in to help with debugging
        Log::debug('Normalizing question data', [
            'raw_data' => $questionData,
            'order' => $order,
            'id_type' => isset($questionData['id']) ? gettype($questionData['id']) : 'not set',
            'id_value' => isset($questionData['id']) ? $questionData['id'] : 'not set'
        ]);
        
        $result = [
            'order' => $order
        ];
        
        // Handle ID - only take numeric IDs, ignore UUIDs from frontend
        if (isset($questionData['id'])) {
            // Check if it's a numeric ID (from database)
            if (is_numeric($questionData['id'])) {
                $result['id'] = $questionData['id'];
                Log::debug('Using existing numeric ID', ['id' => $questionData['id']]);
            } else {
                // It's a UUID or other temporary ID - log but don't include
                Log::debug('Ignoring temporary ID', ['id' => $questionData['id']]);
            }
        }
        
        // Handle question type
        if (isset($questionData['question_type'])) {
            $result['question_type'] = $questionData['question_type'];
            Log::debug('Using question_type from data', ['type' => $questionData['question_type']]);
        } elseif (isset($questionData['type'])) {
            // Map Vue component question types to database question types
            $typeMap = [
                'short-text' => 'text',
                'long-text' => 'textarea',
                'radio' => 'radio',
                'checkbox' => 'checkbox',
                'dropdown' => 'dropdown',
                'rating' => 'rating',
                'date' => 'date',
                'file-upload' => 'file',
                'matrix' => 'matrix',
                'email' => 'text',
                'phone' => 'text',
                'number' => 'text',
                'yes-no' => 'radio',
                'slider' => 'rating',
                'likert' => 'likert'
            ];
            
            $frontendType = $questionData['type'];
            $result['question_type'] = isset($typeMap[$frontendType]) ? $typeMap[$frontendType] : 'text';
            Log::debug('Mapped frontend type to backend type', [
                'frontend_type' => $frontendType,
                'backend_type' => $result['question_type']
            ]);
        } else {
            Log::warning('No question type provided, defaulting to text');
            $result['question_type'] = 'text';
        }
        
        // Handle title/text
        if (isset($questionData['title'])) {
            $result['title'] = $questionData['title'];
        } elseif (isset($questionData['text'])) {
            $result['title'] = $questionData['text'];
        } else {
            $result['title'] = 'Untitled Question'; // Provide a default
            Log::warning('No title provided for question, using default');
        }
        
        // Handle description/helpText
        if (isset($questionData['description'])) {
            $result['description'] = $questionData['description'];
        } elseif (isset($questionData['helpText'])) {
            $result['description'] = $questionData['helpText'];
        }
        
        // Handle required
        if (isset($questionData['is_required'])) {
            $result['is_required'] = (bool)$questionData['is_required'];
        } elseif (isset($questionData['required'])) {
            $result['is_required'] = (bool)$questionData['required'];
        } else {
            $result['is_required'] = false; // Default to not required
        }
        
        // Handle options
        if (isset($questionData['options']) && is_array($questionData['options'])) {
            $options = [];
            foreach ($questionData['options'] as $optionOrder => $option) {
                // Log option structure
                Log::debug('Processing option', [
                    'option_data' => $option,
                    'option_order' => $optionOrder
                ]);
                
                $normalizedOption = [
                    'order' => $optionOrder
                ];
                
                // Handle option ID
                if (isset($option['id']) && is_numeric($option['id'])) {
                    $normalizedOption['id'] = $option['id'];
                }
                
                // Handle value - ensure it's not empty
                if (isset($option['value']) && !empty($option['value'])) {
                    // If value follows the option_X pattern and we have text/label, use text/label as value
                    if (preg_match('/^option_\d+$/', $option['value'])) {
                        if (isset($option['text'])) {
                            $normalizedOption['value'] = $option['text'];
                        } elseif (isset($option['label'])) {
                            $normalizedOption['value'] = $option['label'];
                        } else {
                            $normalizedOption['value'] = $option['value'];
                        }
                    } else {
                        $normalizedOption['value'] = $option['value'];
                    }
                } elseif (isset($option['text'])) {
                    // If no value but text exists, use that as the value
                    $normalizedOption['value'] = $option['text'];
                }
                
                // Handle label/text
                if (isset($option['label'])) {
                    $normalizedOption['label'] = $option['label'];
                } elseif (isset($option['text'])) {
                    $normalizedOption['label'] = $option['text'];
                } elseif (isset($normalizedOption['value'])) {
                    // If no label but value exists, use that as the label
                    $normalizedOption['label'] = $normalizedOption['value'];
                }
                
                // Only add options that have at least a value or label
                if (!empty($normalizedOption['value']) || !empty($normalizedOption['label'])) {
                    $options[] = $normalizedOption;
                }
            }
            $result['options'] = $options;
        }
        
        // Build clean settings object - avoiding nested settings
        $settings = [];
        
        // Primary settings that should be both in settings JSON and as columns
        $primarySettings = [
            'text' => 'text',
            'helpText' => 'helpText',
            'required' => 'required',
            'type' => 'type'
        ];
        
        // First, copy essential fields to the settings
        foreach ($primarySettings as $frontendField => $settingsField) {
            if (isset($questionData[$frontendField])) {
                $settings[$settingsField] = $questionData[$frontendField];
            }
        }
        
        // Then add the frontend type to settings
        if (isset($questionData['type'])) {
            $settings['type'] = $questionData['type'];
        }
        
        // Copy specific field sets based on question type
        if (isset($questionData['type'])) {
            switch ($questionData['type']) {
                case 'radio':
                case 'checkbox':
                case 'dropdown':
                    // These question types have options and option-related settings
                    $optionFieldSets = ['options', 'allowOther', 'allowNone', 'optionsOrder', 'defaultValue'];
                    foreach ($optionFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    break;
                    
                case 'rating':
                    // Rating specific settings
                    $ratingFieldSets = ['maxRating', 'showValues', 'icon', 'defaultValue', 'step'];
                    foreach ($ratingFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    break;
                    
                case 'matrix':
                    // Matrix specific settings
                    $matrixFieldSets = ['matrixType', 'rows', 'columns'];
                    foreach ($matrixFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    break;
                    
                case 'file-upload':
                    // File upload specific settings
                    $fileFieldSets = ['allowedTypes', 'maxFiles', 'maxSize'];
                    foreach ($fileFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    break;
                    
                case 'short-text':
                case 'long-text':
                case 'email':
                case 'phone':
                case 'number':
                    // Text input specific settings
                    $textFieldSets = ['placeholder', 'defaultValue', 'minLength', 'maxLength', 'pattern'];
                    foreach ($textFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    break;
                    
                case 'date':
                    // Date specific settings
                    $dateFieldSets = ['format', 'min', 'max', 'defaultValue'];
                    foreach ($dateFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    break;
                    
                case 'likert':
                    // Likert specific settings
                    $likertFieldSets = ['scale', 'statements'];
                    foreach ($likertFieldSets as $field) {
                        if (isset($questionData[$field])) {
                            $settings[$field] = $questionData[$field];
                        }
                    }
                    
                    // Ensure statements have proper structure
                    if (isset($settings['statements']) && !empty($settings['statements'])) {
                        // Statements are fine
                    } else if (isset($settings['text'])) {
                        // Create a default statement from the question text
                        $settings['statements'] = [
                            [
                                'id' => 'statement-' . uniqid(),
                                'text' => $settings['text']
                            ]
                        ];
                    }
                    break;
            }
        }
        
        // Now go through all remaining properties 
        foreach ($questionData as $key => $value) {
            // Skip fields that we've already handled or that are reserved backend column names
            $reservedFields = [
                'id', 'section_id', 'questionnaire_id', 'question_type', 
                'title', 'description', 'is_required', 'order', 'options', 
                'created_at', 'updated_at', 'settings' // Make sure to exclude existing 'settings'
            ];
            
            if (!in_array($key, $reservedFields) && !isset($settings[$key]) && !is_null($value)) {
                $settings[$key] = $value;
            }
        }

        // Make sure settings doesn't have a 'settings' property to avoid nesting
        if (isset($settings['settings'])) {
            // If settings contains a settings object, merge it up one level
            if (is_array($settings['settings'])) {
                foreach ($settings['settings'] as $key => $value) {
                    if (!isset($settings[$key])) {
                        $settings[$key] = $value;
                    }
                }
            }
            // Remove the nested settings property
            unset($settings['settings']);
        }
        
        // Check if we received settings directly
        if (isset($questionData['settings'])) {
            // If it's a string, try to decode it
            if (is_string($questionData['settings'])) {
                try {
                    $decodedSettings = json_decode($questionData['settings'], true);
                    if (is_array($decodedSettings)) {
                        // Check if the decoded settings has a 'settings' property
                        if (isset($decodedSettings['settings'])) {
                            if (is_array($decodedSettings['settings'])) {
                                // Merge up one level
                                foreach ($decodedSettings['settings'] as $key => $value) {
                                    if (!isset($settings[$key])) {
                                        $settings[$key] = $value;
                                    }
                                }
                            } elseif (is_string($decodedSettings['settings'])) {
                                // It's nested another level, try to decode again
                                try {
                                    $doubleDecoded = json_decode($decodedSettings['settings'], true);
                                    if (is_array($doubleDecoded)) {
                                        foreach ($doubleDecoded as $key => $value) {
                                            if (!isset($settings[$key])) {
                                                $settings[$key] = $value;
                                            }
                                        }
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Failed to decode nested settings string', [
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        } else {
                            // Just merge the decoded settings
                            foreach ($decodedSettings as $key => $value) {
                                if (!isset($settings[$key])) {
                                    $settings[$key] = $value;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to decode settings string', [
                        'error' => $e->getMessage()
                    ]);
                }
            } elseif (is_array($questionData['settings'])) {
                // If it's already an array, merge properties
                foreach ($questionData['settings'] as $key => $value) {
                    if (!isset($settings[$key]) && $key !== 'settings') {
                        $settings[$key] = $value;
                    }
                }
            }
        }
        
        if (!empty($settings)) {
            $result['settings'] = json_encode($settings);
            Log::debug('Settings encoded for question', [
                'settings_count' => count($settings),
                'settings_keys' => array_keys($settings)
            ]);
        }
        
        Log::debug('Normalized question data result', ['result' => $result]);
        
        return $result;
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