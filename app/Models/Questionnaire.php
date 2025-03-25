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
        'description',
        'status',
        'start_date',
        'end_date',
        'is_template',
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
    ];

    /**
     * Get the user who created this questionnaire.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sections for this questionnaire.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    /**
     * Get the responses for this questionnaire.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Scope a query to only include draft questionnaires.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include published questionnaires.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include closed questionnaires.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope a query to only include active questionnaires (published and within date range).
     */
    public function scopeActive($query)
    {
        $now = now();
        
        return $query->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Check if the questionnaire is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }
        
        if ($this->end_date && $this->end_date < $now) {
            return false;
        }
        
        return true;
    }

    /**
     * Get all questions from all sections.
     */
    public function getAllQuestions()
    {
        $questions = collect();
        
        foreach ($this->sections as $section) {
            $questions = $questions->merge($section->questions);
        }
        
        return $questions->sortBy(function ($question) {
            return [$question->section->order, $question->order];
        });
    }
}