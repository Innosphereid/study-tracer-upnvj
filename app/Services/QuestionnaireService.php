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
        
        // Dapatkan questionnaire_id dari section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            Log::error('Cannot add question: Section not found', ['sectionId' => $sectionId]);
            throw new \Exception('Section not found');
        }
        
        $questionData['section_id'] = $sectionId;
        $questionData['questionnaire_id'] = $section->questionnaire_id;
        
        /** @var Question $question */
        $question = $this->questionRepository->create($questionData);
        
        // Update JSON representation
        if ($section->questionnaire) {
            $section->questionnaire->storeAsJson();
        }
        
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
        
        // Get existing sections to determine which ones to update vs create
        $existingSections = $this->sectionRepository->getByQuestionnaireId($questionnaireId);
        $existingSectionIds = $existingSections->pluck('id')->toArray();
        $updatedSectionIds = [];
        
        foreach ($sectionsData as $order => $sectionData) {
            // Determine if we need to update or create
            $sectionId = null;
            if (isset($sectionData['id']) && is_numeric($sectionData['id']) && in_array($sectionData['id'], $existingSectionIds)) {
                // Update existing section
                $sectionId = $sectionData['id'];
                $sectionData['order'] = $order;
                $this->updateSection($sectionId, $sectionData);
                $updatedSectionIds[] = $sectionId;
                
                Log::info('Updated existing section', ['section_id' => $sectionId]);
            } else {
                // Create new section
                $sectionData['order'] = $order;
                $section = $this->addSection($questionnaireId, $sectionData);
                $sectionId = $section->id;
                $updatedSectionIds[] = $sectionId;
                
                Log::info('Created new section', ['section_id' => $sectionId]);
            }
            
            // Process questions for this section
            if (isset($sectionData['questions']) && is_array($sectionData['questions'])) {
                $this->processAndSaveQuestions($sectionId, $sectionData['questions']);
            }
        }
        
        // Remove sections that weren't updated (they've been deleted in the UI)
        $sectionsToDelete = array_diff($existingSectionIds, $updatedSectionIds);
        foreach ($sectionsToDelete as $sectionIdToDelete) {
            $this->deleteSection($sectionIdToDelete);
            Log::info('Deleted section that was not in updated data', ['section_id' => $sectionIdToDelete]);
        }
        
        // Generate JSON representation after processing all sections
        $questionnaire = $this->questionnaireRepository->find($questionnaireId);
        if ($questionnaire) {
            $questionnaire->storeAsJson();
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
            return;
        }
        
        $questionnaireId = $section->questionnaire_id;
        
        Log::info('Processing questions for section', [
            'section_id' => $sectionId,
            'questionnaire_id' => $questionnaireId,
            'question_count' => count($questionsData)
        ]);
        
        // Get existing questions to determine which ones to update vs create
        $existingQuestions = $this->questionRepository->getBySectionId($sectionId);
        $existingQuestionIds = $existingQuestions->pluck('id')->toArray();
        $updatedQuestionIds = [];
        
        foreach ($questionsData as $order => $questionData) {
            // Convert data from frontend format to backend format
            $questionData = $this->normalizeQuestionData($questionData, $order);
            
            // Add questionnaire_id to the question data
            $questionData['questionnaire_id'] = $questionnaireId;
            
            // Determine if we need to update or create
            if (isset($questionData['id']) && is_numeric($questionData['id']) && in_array($questionData['id'], $existingQuestionIds)) {
                // Update existing question
                $questionId = $questionData['id'];
                $this->updateQuestion($questionId, $questionData);
                $updatedQuestionIds[] = $questionId;
                
                // Update options if provided
                if (isset($questionData['options']) && is_array($questionData['options'])) {
                    $this->setQuestionOptions($questionId, $questionData['options']);
                }
                
                Log::info('Updated existing question', ['question_id' => $questionId]);
            } else {
                // Create new question
                $question = $this->addQuestion($sectionId, $questionData);
                $questionId = $question->id;
                $updatedQuestionIds[] = $questionId;
                
                // Create options if provided
                if (isset($questionData['options']) && is_array($questionData['options'])) {
                    $this->setQuestionOptions($questionId, $questionData['options']);
                }
                
                Log::info('Created new question', ['question_id' => $questionId]);
            }
        }
        
        // Remove questions that weren't updated (they've been deleted in the UI)
        $questionsToDelete = array_diff($existingQuestionIds, $updatedQuestionIds);
        foreach ($questionsToDelete as $questionIdToDelete) {
            $this->deleteQuestion($questionIdToDelete);
            Log::info('Deleted question that was not in updated data', ['question_id' => $questionIdToDelete]);
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
        $result = [
            'order' => $order
        ];
        
        // Handle ID (only take numeric IDs, ignore UUIDs from frontend)
        if (isset($questionData['id']) && is_numeric($questionData['id'])) {
            $result['id'] = $questionData['id'];
        }
        
        // Handle question type
        if (isset($questionData['question_type'])) {
            $result['question_type'] = $questionData['question_type'];
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
                'likert' => 'matrix'
            ];
            
            $result['question_type'] = $typeMap[$questionData['type']] ?? 'text';
        }
        
        // Handle title/text
        if (isset($questionData['title'])) {
            $result['title'] = $questionData['title'];
        } elseif (isset($questionData['text'])) {
            $result['title'] = $questionData['text'];
        }
        
        // Handle description/helpText
        if (isset($questionData['description'])) {
            $result['description'] = $questionData['description'];
        } elseif (isset($questionData['helpText'])) {
            $result['description'] = $questionData['helpText'];
        }
        
        // Handle required
        if (isset($questionData['is_required'])) {
            $result['is_required'] = $questionData['is_required'];
        } elseif (isset($questionData['required'])) {
            $result['is_required'] = $questionData['required'];
        }
        
        // Handle options
        if (isset($questionData['options']) && is_array($questionData['options'])) {
            $options = [];
            foreach ($questionData['options'] as $optionOrder => $option) {
                $normalizedOption = [
                    'order' => $optionOrder
                ];
                
                // Handle option ID
                if (isset($option['id']) && is_numeric($option['id'])) {
                    $normalizedOption['id'] = $option['id'];
                }
                
                // Handle value
                if (isset($option['value'])) {
                    $normalizedOption['value'] = $option['value'];
                }
                
                // Handle label/text
                if (isset($option['label'])) {
                    $normalizedOption['label'] = $option['label'];
                } elseif (isset($option['text'])) {
                    $normalizedOption['label'] = $option['text'];
                }
                
                $options[] = $normalizedOption;
            }
            $result['options'] = $options;
        }
        
        // Store all other properties in settings
        $settings = [];
        foreach ($questionData as $key => $value) {
            if (!in_array($key, ['id', 'section_id', 'question_type', 'title', 'description', 'is_required', 'order', 'options', 'text', 'helpText', 'required', 'type']) && !is_null($value)) {
                $settings[$key] = $value;
            }
        }
        
        if (!empty($settings)) {
            $result['settings'] = json_encode($settings);
        }
        
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