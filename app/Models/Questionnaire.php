<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'is_template',
        'settings',
        'questionnaire_json',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_template' => 'boolean',
        'settings' => 'json',
        'questionnaire_json' => 'json',
    ];

    /**
     * Get the user who created the questionnaire.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sections for the questionnaire.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    /**
     * Get the responses for the questionnaire.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Determine if the questionnaire is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();

        // If there's no start_date or end_date, it's always active if published
        if (!$this->start_date && !$this->end_date) {
            return true;
        }

        // If there's a start_date but no end_date, it's active after the start_date
        if ($this->start_date && !$this->end_date) {
            return $now->gte($this->start_date);
        }

        // If there's an end_date but no start_date, it's active until the end_date
        if (!$this->start_date && $this->end_date) {
            return $now->lte($this->end_date);
        }

        // If there's both a start_date and an end_date, it's active between the two
        return $now->gte($this->start_date) && $now->lte($this->end_date);
    }

    /**
     * Get all questions for the questionnaire directly.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get all answer details for the questionnaire.
     */
    public function answerDetails(): HasMany
    {
        return $this->hasMany(AnswerDetail::class);
    }

    /**
     * Store the complete questionnaire structure as JSON.
     *
     * This method creates a comprehensive JSON representation of the questionnaire,
     * including all sections, questions, and options. The JSON is stored in the
     * questionnaire_json field for efficient access by frontend components.
     *
     * @return void
     */
    public function storeAsJson(): void
    {
        // First, ensure we have the latest data
        $this->refresh();
        
        // Load sections with questions and options, ordering everything correctly
        $this->load([
            'sections' => function($query) {
                $query->orderBy('order');
            },
            'sections.questions' => function($query) {
                $query->orderBy('order');
            },
            'sections.questions.options' => function($query) {
                $query->orderBy('order');
            }
        ]);
        
        // Parse settings to ensure it's an array
        $settings = $this->settings;
        if (is_string($settings)) {
            $settings = json_decode($settings, true);
        }
        
        if (empty($settings)) {
            $settings = [];
        }
        
        // Create a complete representation
        $jsonData = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'settings' => $settings,
            'sections' => []
        ];
        
        // Process sections, ensuring they have all required data
        foreach ($this->sections as $section) {
            $sectionData = [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'order' => $section->order,
                'questions' => []
            ];
            
            // Process section settings
            if (!empty($section->settings)) {
                $sectionSettings = $section->settings;
                if (is_string($sectionSettings)) {
                    $sectionSettings = json_decode($sectionSettings, true);
                }
                $sectionData['settings'] = $sectionSettings;
            }
            
            // Process questions
            foreach ($section->questions as $question) {
                $questionData = [
                    'id' => $question->id,
                    'type' => $this->mapQuestionType($question->question_type),
                    'question_type' => $question->question_type,
                    'title' => $question->title,
                    'text' => $question->title, // Duplicate for frontend compatibility
                    'description' => $question->description,
                    'helpText' => $question->description, // Duplicate for frontend compatibility
                    'is_required' => (bool)$question->is_required,
                    'required' => (bool)$question->is_required, // Duplicate for frontend compatibility
                    'order' => $question->order,
                    'options' => []
                ];
                
                // Process question settings
                if (!empty($question->settings)) {
                    $questionSettings = $question->settings;
                    if (is_string($questionSettings)) {
                        $questionSettings = json_decode($questionSettings, true);
                    }
                    $questionData['settings'] = $questionSettings;
                    
                    // Apply specific settings to the question root for frontend compatibility
                    if (is_array($questionSettings)) {
                        foreach ($questionSettings as $key => $value) {
                            if (!isset($questionData[$key])) {
                                $questionData[$key] = $value;
                            }
                        }
                    }
                }
                
                // Process options (if any)
                foreach ($question->options as $option) {
                    $optionData = [
                        'id' => $option->id,
                        'text' => $option->label,
                        'value' => $option->value,
                        'order' => $option->order
                    ];
                    
                    $questionData['options'][] = $optionData;
                }
                
                // Add special properties based on question type
                $this->addTypeSpecificProperties($questionData);
                
                $sectionData['questions'][] = $questionData;
            }
            
            $jsonData['sections'][] = $sectionData;
        }
        
        // Store the JSON representation
        $this->questionnaire_json = $jsonData;
        $this->save();
    }
    
    /**
     * Map database question types to frontend question types.
     *
     * @param string $dbType The database question type
     * @return string The frontend question type
     */
    private function mapQuestionType(string $dbType): string
    {
        $typeMap = [
            'text' => 'short-text',
            'textarea' => 'long-text',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'dropdown' => 'dropdown',
            'rating' => 'rating',
            'date' => 'date',
            'file' => 'file-upload',
            'matrix' => 'matrix',
            'likert' => 'likert',
            'yes-no' => 'yes-no',
            'slider' => 'slider'
        ];
        
        return $typeMap[$dbType] ?? 'short-text';
    }
    
    /**
     * Add type-specific properties to question data based on its type.
     *
     * @param array &$questionData The question data to enhance
     * @return void
     */
    private function addTypeSpecificProperties(array &$questionData): void
    {
        switch ($questionData['question_type']) {
            case 'matrix':
                if (!isset($questionData['rows'])) {
                    $questionData['rows'] = [
                        ['id' => 'row1', 'text' => 'Row 1'],
                        ['id' => 'row2', 'text' => 'Row 2']
                    ];
                }
                
                if (!isset($questionData['columns'])) {
                    $questionData['columns'] = [
                        ['id' => 'col1', 'text' => 'Column 1'],
                        ['id' => 'col2', 'text' => 'Column 2']
                    ];
                }
                
                if (!isset($questionData['matrixType'])) {
                    $questionData['matrixType'] = 'radio';
                }
                break;
                
            case 'rating':
                if (!isset($questionData['maxRating'])) {
                    $questionData['maxRating'] = 5;
                }
                
                if (!isset($questionData['labels'])) {
                    $questionData['labels'] = [
                        '1' => 'Sangat Buruk',
                        '5' => 'Sangat Baik'
                    ];
                }
                break;
                
            case 'likert':
                if (!isset($questionData['statements']) && isset($questionData['text'])) {
                    $questionData['statements'] = [
                        ['id' => 'statement1', 'text' => $questionData['text']]
                    ];
                }
                
                if (!isset($questionData['scale'])) {
                    $questionData['scale'] = [
                        ['value' => 1, 'label' => 'Sangat Tidak Setuju'],
                        ['value' => 2, 'label' => 'Tidak Setuju'],
                        ['value' => 3, 'label' => 'Netral'],
                        ['value' => 4, 'label' => 'Setuju'],
                        ['value' => 5, 'label' => 'Sangat Setuju']
                    ];
                }
                break;
                
            case 'file':
                if (!isset($questionData['allowedTypes'])) {
                    $questionData['allowedTypes'] = ['image/*', 'application/pdf'];
                }
                
                if (!isset($questionData['maxSize'])) {
                    $questionData['maxSize'] = 5; // 5MB
                }
                
                if (!isset($questionData['maxFiles'])) {
                    $questionData['maxFiles'] = 1;
                }
                break;
                
            case 'slider':
                if (!isset($questionData['min'])) {
                    $questionData['min'] = 0;
                }
                
                if (!isset($questionData['max'])) {
                    $questionData['max'] = 100;
                }
                
                if (!isset($questionData['step'])) {
                    $questionData['step'] = 1;
                }
                
                if (!isset($questionData['showTicks'])) {
                    $questionData['showTicks'] = true;
                }
                break;
        }
    }

    /**
     * Calculate the response rate for this questionnaire.
     *
     * @return float
     */
    public function getResponseRate(): float
    {
        $totalResponses = $this->responses()->count();
        $completedResponses = $this->responses()->whereNotNull('completed_at')->count();
        
        if ($totalResponses === 0) {
            return 0;
        }
        
        return round(($completedResponses / $totalResponses) * 100, 1);
    }

    /**
     * Get the total number of questions across all sections.
     *
     * @return int
     */
    public function getQuestionsCountAttribute(): int
    {
        // If the attribute already exists, use it
        if (array_key_exists('questions_count', $this->attributes)) {
            return $this->attributes['questions_count'];
        }
        
        // If we have the relationship loaded with withCount, use that
        if ($this->questions_count !== null) {
            return $this->questions_count;
        }
        
        // If sections are loaded, calculate from them
        if ($this->relationLoaded('sections')) {
            // Check if questions are loaded on the sections
            $firstSection = $this->sections->first();
            if ($firstSection && $firstSection->relationLoaded('questions')) {
                return $this->sections->sum(function ($section) {
                    return $section->questions->count();
                });
            }
            
            // Otherwise, load the sections with questions count
            return $this->sections->loadCount('questions')->sum('questions_count');
        }
        
        // As a last resort, query directly
        return $this->sections()->withCount('questions')->get()->sum('questions_count');
    }
} 