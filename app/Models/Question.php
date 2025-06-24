<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_id',
        'questionnaire_id',
        'question_type',
        'title',
        'description',
        'is_required',
        'order',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * The section that the question belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * The options for the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order');
    }

    /**
     * The logic conditions for the question.
     */
    public function logic(): HasMany
    {
        return $this->hasMany(QuestionLogic::class);
    }

    /**
     * The answers to this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(AnswerData::class);
    }

    /**
     * Determine if the question has options.
     *
     * @return bool
     */
    public function hasOptions(): bool
    {
        return in_array($this->question_type, ['radio', 'checkbox', 'dropdown', 'matrix', 'likert']);
    }

    /**
     * The questionnaire that the question belongs to directly.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * The answer details for this question.
     */
    public function answerDetails(): HasMany
    {
        return $this->hasMany(AnswerDetail::class);
    }

    /**
     * Get the parent questionnaire through the section.
     * 
     * @deprecated Use the questionnaire() relationship instead
     */
    public function getQuestionnaire()
    {
        return $this->section->questionnaire;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // When a question is deleted, also delete all its options and logic
        static::deleting(function ($question) {
            $question->options()->delete();
            $question->logic()->delete();
        });
    }
} 