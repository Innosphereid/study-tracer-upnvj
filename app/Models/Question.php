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
        return in_array($this->question_type, ['radio', 'checkbox', 'dropdown', 'matrix']);
    }

    /**
     * Get the parent questionnaire through the section.
     */
    public function questionnaire()
    {
        return $this->section->questionnaire;
    }
} 