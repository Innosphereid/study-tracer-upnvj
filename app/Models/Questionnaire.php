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
     * @return void
     */
    public function storeAsJson(): void
    {
        // Load sections with questions and options
        $this->load(['sections.questions.options']);
        
        // Create a complete representation
        $jsonData = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'settings' => $this->settings,
            'sections' => $this->sections->toArray()
        ];
        
        // Store the JSON representation
        $this->questionnaire_json = $jsonData;
        $this->save();
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