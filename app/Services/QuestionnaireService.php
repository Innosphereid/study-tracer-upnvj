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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        
        // Get the questionnaire with count relationships
        $questionnaire = $this->questionnaireRepository->find($id, ['*'], [
            'user',
            'sections'
        ]);
        
        if ($questionnaire) {
            // Add count information
            $questionnaire->loadCount('sections', 'responses');
            
            // Calculate questions count
            $questionnaire->loadCount(['sections as questions_count' => function($query) {
                $query->withCount('questions');
            }]);
        }
        
        return $questionnaire;
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
                    $data['settings'] = json_encode($settingsData, JSON_UNESCAPED_SLASHES);
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
            
            // Process data for update using transaction
            return DB::transaction(function() use ($id, $data, $questionnaire) {
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
                    // Ensure settings is properly formatted as JSON
                    if (is_array($data['settings'])) {
                        $updateData['settings'] = json_encode($data['settings'], JSON_UNESCAPED_SLASHES);
                    } else {
                        $updateData['settings'] = $data['settings'];
                    }
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
                    
                    // Process and save sections, handling new and temporary IDs
                    $this->processAndSaveSections($id, $data['sections']);
                    
                    // Update JSON representation
                    $questionnaire->refresh();
                    $questionnaire->storeAsJson();
                }
                
                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error updating questionnaire', [
                'id' => $id,
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
            
            // Validate question_type to ensure it's one of the allowed enum values
            $allowedTypes = ['text', 'textarea', 'radio', 'checkbox', 'dropdown', 'rating', 'date', 'file', 'matrix', 'likert', 'yes-no', 'slider', 'ranking'];
            
            if (isset($questionData['question_type'])) {
                if (!in_array($questionData['question_type'], $allowedTypes)) {
                    Log::warning('Invalid question_type, converting to text', [
                        'original_type' => $questionData['question_type'],
                        'converted_to' => 'text'
                    ]);
                    $questionData['question_type'] = 'text';
                }
                
                // Special handling for likert questions to ensure they have required structure
                if ($questionData['question_type'] === 'likert') {
                    // Ensure settings contains type=likert
                    if (!isset($questionData['settings'])) {
                        $questionData['settings'] = json_encode([
                            'type' => 'likert',
                            'scale' => [
                                ['value' => 1, 'label' => 'Sangat Tidak Setuju'],
                                ['value' => 2, 'label' => 'Tidak Setuju'],
                                ['value' => 3, 'label' => 'Netral'],
                                ['value' => 4, 'label' => 'Setuju'],
                                ['value' => 5, 'label' => 'Sangat Setuju']
                            ],
                            'statements' => [
                                [
                                    'id' => 'statement-' . uniqid(),
                                    'text' => $questionData['title'] ?? 'Default Statement'
                                ]
                            ]
                        ], JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Created default settings for likert question', [
                            'settings' => $questionData['settings']
                        ]);
                    } else if (is_array($questionData['settings'])) {
                        // If settings is an array, ensure it has the required structure and encode as JSON
                        $settings = $questionData['settings'];
                        $settings['type'] = 'likert';
                        
                        if (!isset($settings['scale']) || !is_array($settings['scale'])) {
                            $settings['scale'] = [
                                ['value' => 1, 'label' => 'Sangat Tidak Setuju'],
                                ['value' => 2, 'label' => 'Tidak Setuju'],
                                ['value' => 3, 'label' => 'Netral'],
                                ['value' => 4, 'label' => 'Setuju'],
                                ['value' => 5, 'label' => 'Sangat Setuju']
                            ];
                        }
                        
                        if (!isset($settings['statements']) || !is_array($settings['statements'])) {
                            $settings['statements'] = [
                                [
                                    'id' => 'statement-' . uniqid(),
                                    'text' => $questionData['title'] ?? 'Default Statement'
                                ]
                            ];
                        }
                        
                        $questionData['settings'] = json_encode($settings, JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Normalized settings for likert question', [
                            'settings' => $questionData['settings']
                        ]);
                    }
                }
                // Special handling for yes-no questions
                else if ($questionData['question_type'] === 'yes-no') {
                    // Ensure settings contains type=yes-no
                    if (!isset($questionData['settings'])) {
                        $questionData['settings'] = json_encode([
                            'type' => 'yes-no',
                            'yesLabel' => 'Ya',
                            'noLabel' => 'Tidak'
                        ], JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Created default settings for yes-no question', [
                            'settings' => $questionData['settings']
                        ]);
                    } else if (is_array($questionData['settings'])) {
                        // If settings is an array, ensure it has the required structure and encode as JSON
                        $settings = $questionData['settings'];
                        $settings['type'] = 'yes-no';
                        
                        if (!isset($settings['yesLabel'])) {
                            $settings['yesLabel'] = 'Ya';
                        }
                        
                        if (!isset($settings['noLabel'])) {
                            $settings['noLabel'] = 'Tidak';
                        }
                        
                        $questionData['settings'] = json_encode($settings, JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Normalized settings for yes-no question', [
                            'settings' => $questionData['settings']
                        ]);
                    }
                }
                // Special handling for slider questions
                else if ($questionData['question_type'] === 'slider') {
                    // Ensure settings contains type=slider
                    if (!isset($questionData['settings'])) {
                        $questionData['settings'] = json_encode([
                            'type' => 'slider',
                            'min' => 0,
                            'max' => 100,
                            'step' => 1,
                            'showTicks' => true,
                            'showLabels' => true,
                            'labels' => [
                                '0' => 'Minimum',
                                '100' => 'Maximum'
                            ]
                        ], JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Created default settings for slider question', [
                            'settings' => $questionData['settings']
                        ]);
                    } else if (is_array($questionData['settings'])) {
                        // If settings is an array, ensure it has the required structure and encode as JSON
                        $settings = $questionData['settings'];
                        $settings['type'] = 'slider';
                        
                        if (!isset($settings['min'])) {
                            $settings['min'] = 0;
                        }
                        
                        if (!isset($settings['max'])) {
                            $settings['max'] = 100;
                        }
                        
                        if (!isset($settings['step'])) {
                            $settings['step'] = 1;
                        }
                        
                        if (!isset($settings['showTicks'])) {
                            $settings['showTicks'] = true;
                        }
                        
                        if (!isset($settings['showLabels'])) {
                            $settings['showLabels'] = true;
                        }
                        
                        if (!isset($settings['labels'])) {
                            $settings['labels'] = [
                                $settings['min'] => 'Minimum',
                                $settings['max'] => 'Maximum'
                            ];
                        }
                        
                        $questionData['settings'] = json_encode($settings, JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Normalized settings for slider question', [
                            'settings' => $questionData['settings']
                        ]);
                    }
                }
                // Special handling for ranking questions
                else if ($questionData['question_type'] === 'ranking') {
                    // Ensure settings contains type=ranking
                    if (!isset($questionData['settings'])) {
                        $questionData['settings'] = json_encode([
                            'type' => 'ranking',
                            'options' => [
                                ['id' => 'item-' . uniqid(), 'text' => 'Item 1'],
                                ['id' => 'item-' . uniqid(), 'text' => 'Item 2'],
                                ['id' => 'item-' . uniqid(), 'text' => 'Item 3']
                            ]
                        ], JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Created default settings for ranking question', [
                            'settings' => $questionData['settings']
                        ]);
                    } else if (is_array($questionData['settings'])) {
                        // If settings is an array, ensure it has the required structure and encode as JSON
                        $settings = $questionData['settings'];
                        $settings['type'] = 'ranking';
                        
                        if (!isset($settings['options']) || !is_array($settings['options']) || empty($settings['options'])) {
                            $settings['options'] = [
                                ['id' => 'item-' . uniqid(), 'text' => 'Item 1'],
                                ['id' => 'item-' . uniqid(), 'text' => 'Item 2'],
                                ['id' => 'item-' . uniqid(), 'text' => 'Item 3']
                            ];
                        }
                        
                        $questionData['settings'] = json_encode($settings, JSON_UNESCAPED_SLASHES);
                        
                        Log::info('Normalized settings for ranking question', [
                            'settings' => $questionData['settings']
                        ]);
                    }
                }
            }
            
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
            // Special handling for allowedTypes with */*
            if (isset($questionData['settings']['allowedTypes']) && is_array($questionData['settings']['allowedTypes'])) {
                if (in_array('*/*', $questionData['settings']['allowedTypes'])) {
                    Log::info('Found */* in allowedTypes, ensuring proper JSON encoding');
                    // Force to exactly ['*/*'] to prevent mixed types
                    $questionData['settings']['allowedTypes'] = ['*/*'];
                }
            }
            
            // Tambahkan JSON_UNESCAPED_SLASHES untuk mencegah escape pada "*/*"
            $questionData['settings'] = json_encode($questionData['settings'], JSON_UNESCAPED_SLASHES);
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
                $publishData['settings'] = is_string($publishData['settings']) ? json_encode($settings, JSON_UNESCAPED_SLASHES) : $settings;
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
            // Ensure slug is properly formatted
            $updateData['slug'] = Str::slug($publishData['slug']);
        }
        
        if (isset($publishData['settings'])) {
            $updateData['settings'] = is_string($publishData['settings']) 
                ? $publishData['settings'] 
                : json_encode($publishData['settings'], JSON_UNESCAPED_SLASHES);
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
                            'question_type' => $questionData['question_type'],
                            'question_data' => array_diff_key($questionData, ['options' => []])
                        ]);
                        
                        // Special handling for likert question type
                        if ($questionData['question_type'] === 'likert') {
                            Log::info('Creating new likert question', [
                                'section_id' => $sectionId,
                                'title' => $questionData['title'] ?? 'Untitled Likert Question',
                                'has_settings' => isset($questionData['settings']),
                                'settings_type' => isset($questionData['settings']) ? gettype($questionData['settings']) : 'none'
                            ]);
                            
                            // Make sure settings are properly encoded as JSON
                            if (isset($questionData['settings']) && is_array($questionData['settings'])) {
                                Log::debug('Encoding likert settings from array to JSON', [
                                    'settings_keys' => array_keys($questionData['settings'])
                                ]);
                                $questionData['settings'] = json_encode($questionData['settings'], JSON_UNESCAPED_SLASHES);
                            }
                            
                            // Ensure we have a minimum valid structure in settings if not provided
                            if (!isset($questionData['settings']) || empty($questionData['settings']) || $questionData['settings'] === '[]' || $questionData['settings'] === '{}') {
                                Log::warning('Likert question missing settings, creating default structure', [
                                    'question_title' => $questionData['title'] ?? 'Untitled Likert Question'
                                ]);
                                
                                $defaultSettings = [
                                    'type' => 'likert',
                                    'statements' => [
                                        ['id' => 'statement-' . uniqid(), 'text' => $questionData['title'] ?? 'Default Statement']
                                    ],
                                    'scale' => [
                                        ['value' => 1, 'label' => 'Sangat Tidak Setuju'],
                                        ['value' => 2, 'label' => 'Tidak Setuju'],
                                        ['value' => 3, 'label' => 'Netral'],
                                        ['value' => 4, 'label' => 'Setuju'],
                                        ['value' => 5, 'label' => 'Sangat Setuju']
                                    ]
                                ];
                                
                                $questionData['settings'] = json_encode($defaultSettings, JSON_UNESCAPED_SLASHES);
                            }
                        }
                        
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
     * This method handles the conversion from frontend Vue component structure
     * to the format expected by the database models, dealing with different field
     * names and ensuring correct data types.
     *
     * @param array $questionData Frontend question data
     * @param int $order The order position of the question
     * @return array Normalized question data for the backend
     */
    private function normalizeQuestionData(array $questionData, int $order): array
    {
        $result = [
            'order' => $order
        ];
        
        // Log the input data for debugging
        Log::debug('Normalizing question data', [
            'question_data_keys' => array_keys($questionData),
            'has_id' => isset($questionData['id']),
            'has_type' => isset($questionData['type']) || isset($questionData['question_type']),
            'type' => isset($questionData['type']) ? $questionData['type'] : (isset($questionData['question_type']) ? $questionData['question_type'] : 'unknown')
        ]);
        
        // Copy ID if provided and it's numeric (valid database ID)
        if (isset($questionData['id']) && is_numeric($questionData['id'])) {
            $result['id'] = (int)$questionData['id'];
        }
        
        // Map question type from frontend to backend
        if (isset($questionData['type'])) {
            $result['question_type'] = $this->mapBackendQuestionType($questionData['type']);
        } elseif (isset($questionData['question_type'])) {
            $result['question_type'] = $questionData['question_type'];
        } else {
            // Default to text if no type specified
            $result['question_type'] = 'text';
            Log::warning('Question missing type, defaulting to text', [
                'question_id' => $questionData['id'] ?? 'new',
                'question_title' => $questionData['title'] ?? $questionData['text'] ?? 'Untitled'
            ]);
        }
        
        // Map text or title field
        if (isset($questionData['text'])) {
            $result['title'] = $questionData['text'];
        } elseif (isset($questionData['title'])) {
            $result['title'] = $questionData['title'];
        } else {
            $result['title'] = 'Untitled Question';
            Log::warning('Question missing title/text, using default', [
                'question_id' => $questionData['id'] ?? 'new'
            ]);
        }
        
        // Map description or helpText field
        if (isset($questionData['helpText'])) {
            $result['description'] = $questionData['helpText'];
        } elseif (isset($questionData['description'])) {
            $result['description'] = $questionData['description'];
        } else {
            $result['description'] = '';
        }
        
        // Map required field
        if (isset($questionData['required'])) {
            $result['is_required'] = (bool)$questionData['required'];
        } elseif (isset($questionData['is_required'])) {
            $result['is_required'] = (bool)$questionData['is_required'];
        } else {
            $result['is_required'] = false;
        }

        // Handle options for choice-based questions
        if (isset($questionData['options']) && is_array($questionData['options'])) {
            $result['options'] = [];
            foreach ($questionData['options'] as $i => $option) {
                $newOption = [];
                
                // Only include ID if it's numeric (valid database ID)
                if (isset($option['id']) && is_numeric($option['id'])) {
                    $newOption['id'] = (int)$option['id'];
                }
                
                // Map option text/label & value
                if (isset($option['text'])) {
                    $newOption['label'] = $option['text'];
                } elseif (isset($option['label'])) {
                    $newOption['label'] = $option['label'];
                } else {
                    $newOption['label'] = "Option " . ($i + 1);
                }
                
                if (isset($option['value'])) {
                    $newOption['value'] = $option['value'];
                } else {
                    $newOption['value'] = $newOption['label'];
                }
                
                // Set order
                $newOption['order'] = $option['order'] ?? $i;
                
                $result['options'][] = $newOption;
            }
        }
        
        // Process settings into a format that can be stored in the database
        $settings = [];
        
        // Collect specific question settings based on type
        $result = $this->processQuestionTypeSettings($questionData, $result);
        
        return $result;
    }

    /**
     * Maps frontend question types to backend database types
     *
     * @param string $frontendType The question type from the frontend
     * @return string The corresponding backend/database question type
     */
    private function mapBackendQuestionType(string $frontendType): string
    {
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
            'likert' => 'likert',
            'yes-no' => 'yes-no',
            'slider' => 'slider',
            'ranking' => 'ranking',
            'email' => 'text',
            'phone' => 'text',
            'number' => 'text'
        ];
        
        return $typeMap[$frontendType] ?? 'text';
    }
    
    /**
     * Process question type specific settings and add them to the result
     *
     * @param array $questionData Frontend question data 
     * @param array $result The result array being built
     * @return array Updated result with settings
     */
    private function processQuestionTypeSettings(array $questionData, array $result): array
    {
        $settings = [];
        
        // Add the frontend type to settings if it exists
        if (isset($questionData['type'])) {
            $settings['type'] = $questionData['type'];
        }
        
        // Process specific settings based on question type
        $type = $questionData['type'] ?? $questionData['question_type'] ?? 'text';
        
        switch ($type) {
            case 'radio':
            case 'checkbox':
            case 'dropdown':
                // Options-based question settings
                foreach (['allowOther', 'allowNone', 'optionsOrder', 'defaultValue'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                break;
                
            case 'rating':
                // Rating specific settings
                foreach (['maxRating', 'showValues', 'icon', 'defaultValue', 'step'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                break;
                
            case 'matrix':
                // Matrix specific settings
                foreach (['matrixType', 'rows', 'columns'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                break;
                
            case 'file-upload':
                // File upload specific settings
                foreach (['allowedTypes', 'maxFiles', 'maxSize'] as $field) {
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
                foreach (['placeholder', 'defaultValue', 'minLength', 'maxLength', 'pattern'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                break;
                
            case 'date':
                // Date specific settings
                foreach (['format', 'min', 'max', 'defaultValue'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                break;
                
            case 'yes-no':
                // Yes-No specific settings
                foreach (['yesLabel', 'noLabel', 'defaultValue'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                
                // Default labels
                if (!isset($settings['yesLabel'])) {
                    $settings['yesLabel'] = 'Ya';
                }
                if (!isset($settings['noLabel'])) {
                    $settings['noLabel'] = 'Tidak';
                }
                break;
                
            case 'slider':
                // Slider specific settings
                foreach (['min', 'max', 'step', 'showTicks', 'showLabels', 'labels', 'defaultValue'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                
                // Default values
                if (!isset($settings['min'])) {
                    $settings['min'] = 0;
                }
                if (!isset($settings['max'])) {
                    $settings['max'] = 100;
                }
                if (!isset($settings['step'])) {
                    $settings['step'] = 1;
                }
                break;
                
            case 'ranking':
                // Ranking specific settings
                if (isset($questionData['options'])) {
                    $settings['options'] = $questionData['options'];
                }
                break;
                
            case 'likert':
                // Likert specific settings
                foreach (['scale', 'statements', 'leftLabel', 'rightLabel'] as $field) {
                    if (isset($questionData[$field])) {
                        $settings[$field] = $questionData[$field];
                    }
                }
                break;
        }
        
        // If we have settings, encode them to JSON
        if (!empty($settings)) {
            $result['settings'] = json_encode($settings, JSON_UNESCAPED_SLASHES);
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

    /**
     * Get total questionnaires count.
     *
     * @return int
     */
    public function getTotalQuestionnairesCount(): int
    {
        return app(\App\Models\Questionnaire::class)->count();
    }

    /**
     * Get active questionnaires count.
     *
     * @return int
     */
    public function getActiveQuestionnairesCount(): int
    {
        return app(\App\Models\Questionnaire::class)
            ->where('status', 'published')
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    $q->whereNull('start_date')
                      ->whereNull('end_date');
                })->orWhere(function ($q) use ($now) {
                    $q->whereNull('end_date')
                      ->where('start_date', '<=', $now);
                })->orWhere(function ($q) use ($now) {
                    $q->whereNull('start_date')
                      ->where('end_date', '>=', $now);
                })->orWhere(function ($q) use ($now) {
                    $q->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now);
                });
            })
            ->count();
    }

    /**
     * Get total responses count.
     *
     * @return int
     */
    public function getTotalResponsesCount(): int
    {
        return app(\App\Models\Response::class)->count();
    }

    /**
     * Get overall response rate.
     *
     * @return float
     */
    public function getOverallResponseRate(): float
    {
        $totalResponses = $this->getTotalResponsesCount();
        if ($totalResponses === 0) {
            return 0;
        }
        
        $completedResponses = app(\App\Models\Response::class)
            ->whereNotNull('completed_at')
            ->count();
            
        return round(($completedResponses / $totalResponses) * 100, 1);
    }
    
    /**
     * Get filtered and paginated questionnaires
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredQuestionnaires(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        Log::info('Getting filtered and paginated questionnaires', [
            'filters' => $filters,
            'perPage' => $perPage
        ]);
        
        return $this->questionnaireRepository->getFiltered($filters, $perPage, ['*'], ['user']);
    }
} 